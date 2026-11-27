<?php

test('api v1 health returns ok with correlation id', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertHeader('X-Correlation-ID')
        ->assertJsonStructure([
            'status',
            'service',
            'version',
            'database',
            'timestamp',
            'correlation_id',
        ])
        ->assertJsonPath('service', 'SPIN MEIS 2.0');
});

test('openapi spec is served and includes health endpoint', function () {
    $response = $this->get('/api/v1/openapi.json');

    $response->assertOk();
    $data = json_decode($response->getContent(), true);
    expect($data['openapi'])->toBe('3.1.0');
    expect($data['paths'])->toHaveKey('/health');
});

test('api returns standard error format on 404', function () {
    $response = $this->getJson('/api/v1/nonexistent-endpoint');

    $response->assertNotFound()
        ->assertJsonStructure([
            'code',
            'message',
            'field_errors',
            'correlation_id',
        ])
        ->assertJsonPath('code', 'NOT_FOUND');
});

test('all prd section 5 menu routes render successfully', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/',
    '/categories/results-framework',
    '/categories/communication-framework',
    '/categories/behaviour-change-communication',
    '/categories/grievance-mechanism-kpis',
    '/categories/gender-social-inclusion',
    '/impact-indicators',
    '/project-data/river-basin',
    '/project-data/procurement',
    '/project-data/awpb',
    '/reports',
    '/admin/users',
    '/admin/integrations',
    '/admin/data-backup',
]);

test('pwa manifest and service worker exist in public directory', function () {
    expect(file_exists(public_path('manifest.json')))->toBeTrue();
    expect(file_exists(public_path('sw.js')))->toBeTrue();

    $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
    expect($manifest['name'])->toBe('SPIN MEIS 2.0');
});
