<?php

use App\Models\AuditLog;
use App\Models\Rbda;
use App\Models\Scheme;
use App\Models\State;
use App\Models\User;
use App\Policies\OwnerScopedPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('state user cannot list or call API for another owner records across model 1 and model 2 schemes', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $jigawa = State::create(['name' => 'Jigawa', 'code' => 'JG']);
    $rbda = Rbda::create(['name' => 'Hadejia-Jamaare RBDA', 'code' => 'HJRBDA']);

    // Model 1 scheme -> owned by RBDA
    $model1Scheme = Scheme::create([
        'name' => 'Kano River Irrigation Project',
        'code' => 'KRIP-1',
        'state_id' => $kano->id,
        'rbda_id' => $rbda->id,
        'delivery_model' => 'model_1',
    ]);

    // Model 2 scheme -> owned by Kano State
    $kanoScheme = Scheme::create([
        'name' => 'Kano State Small Scheme',
        'code' => 'KSSS-2',
        'state_id' => $kano->id,
        'rbda_id' => $rbda->id,
        'delivery_model' => 'model_2',
    ]);

    // Model 2 scheme -> owned by Jigawa State
    $jigawaScheme = Scheme::create([
        'name' => 'Jigawa State Scheme',
        'code' => 'JSS-2',
        'state_id' => $jigawa->id,
        'rbda_id' => $rbda->id,
        'delivery_model' => 'model_2',
    ]);

    $kanoUser = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $kanoUser->assignRole('SPIU M&E Officer');

    $this->actingAs($kanoUser);

    // Query scope filters to Kano Model 2 only (not RBDA Model 1, not Jigawa Model 2)
    $visibleIds = Scheme::query()->pluck('id')->all();
    expect($visibleIds)->toBe([$kanoScheme->id]);

    // API list returns only Kano's Model 2 scheme
    $this->getJson('/api/v1/schemes')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $kanoScheme->id);

    // API show for RBDA Model 1 scheme and Jigawa Model 2 scheme are forbidden
    $this->getJson("/api/v1/schemes/{$model1Scheme->id}")->assertForbidden();
    $this->getJson("/api/v1/schemes/{$jigawaScheme->id}")->assertForbidden();
});

test('national user sees all records and world bank user cannot create or edit anything', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $rbda = Rbda::create(['name' => 'HJRBDA', 'code' => 'HJ']);

    $s1 = Scheme::create(['name' => 'S1', 'code' => 'S1', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_1']);
    $s2 = Scheme::create(['name' => 'S2', 'code' => 'S2', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_2']);

    $national = User::factory()->create(['owner_type' => 'national', 'owner_id' => null]);
    $national->assignRole('FPMU M&E Specialist');

    $this->actingAs($national);
    expect(Scheme::query()->count())->toBe(2);

    $wb = User::factory()->create(['owner_type' => 'national', 'owner_id' => null]);
    $wb->assignRole('World Bank');

    $this->actingAs($wb);
    expect(Scheme::query()->count())->toBe(2);

    $this->postJson('/api/v1/schemes', [
        'name' => 'Blocked',
        'code' => 'BLK',
        'state_id' => $kano->id,
        'rbda_id' => $rbda->id,
        'delivery_model' => 'model_2',
    ])->assertForbidden();

    $policy = new OwnerScopedPolicy;
    expect($policy->createRecord($wb))->toBeFalse();
    expect($policy->updateRecord($wb, $s1))->toBeFalse();
    expect($policy->deleteRecord($wb, $s2))->toBeFalse();
});

test('one user cannot both submit and verify the same record', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $user->assignRole('SPIU M&E Officer');

    $otherVerifier = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $otherVerifier->assignRole('SPIU M&E Officer');

    $record = new class extends Model
    {
        protected $guarded = [];
    };
    $record->forceFill([
        'owner_type' => 'state',
        'owner_id' => $kano->id,
        'submitted_by' => $user->id,
        'verified_by' => null,
    ]);

    $policy = new OwnerScopedPolicy;

    expect($policy->verify($user, $record))->toBeFalse();
    expect($policy->verify($otherVerifier, $record))->toBeTrue();
});

test('audit entries exist for sign-in, role change, create, update, and delete', function () {
    $user = User::factory()->create(['owner_type' => 'national', 'owner_id' => null]);
    $this->actingAs($user);

    // Sign-in
    Event::dispatch(new Login('web', $user, false));
    expect(AuditLog::where('event', 'auth.login')->where('user_id', $user->id)->exists())->toBeTrue();

    // Role change
    $user->assignRole('Administrator');
    expect(AuditLog::where('event', 'role.assigned')->where('auditable_id', $user->id)->exists())->toBeTrue();

    // Create, Update, Delete
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    expect(AuditLog::where('event', 'model.created')->where('auditable_type', State::class)->where('auditable_id', $state->id)->exists())->toBeTrue();

    $state->update(['name' => 'Kano State']);
    expect(AuditLog::where('event', 'model.updated')->where('auditable_type', State::class)->where('auditable_id', $state->id)->exists())->toBeTrue();

    $stateId = $state->id;
    $state->delete();
    expect(AuditLog::where('event', 'model.deleted')->where('auditable_type', State::class)->where('auditable_id', $stateId)->exists())->toBeTrue();
});
