<?php

use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\MasterDataManager;
use App\Livewire\Admin\UsersManager;
use App\Livewire\SectionPage;
use Illuminate\Support\Facades\Route;

Route::get('/', SectionPage::class)
    ->defaults('title', 'Dashboard')
    ->defaults('group', 'Overview')
    ->defaults('description', 'Category scorecard and items needing attention.')
    ->name('dashboard');

Route::prefix('categories')->group(function () {
    Route::get('/results-framework', SectionPage::class)
        ->defaults('title', 'Results Framework (PAD)')
        ->defaults('group', 'Indicator Categories')
        ->defaults('description', '24 PDO and Intermediate Result lines reported to the World Bank.')
        ->name('categories.rf');

    Route::get('/communication-framework', SectionPage::class)
        ->defaults('title', 'Communication Framework')
        ->defaults('group', 'Indicator Categories')
        ->defaults('description', '11 indicators covering awareness, engagement, media, and WUA communication.')
        ->name('categories.cf');

    Route::get('/behaviour-change-communication', SectionPage::class)
        ->defaults('title', 'Behaviour Change Communication')
        ->defaults('group', 'Indicator Categories')
        ->defaults('description', '26 indicators covering knowledge, attitudes, practice adoption, and mediation.')
        ->name('categories.bcc');

    Route::get('/grievance-mechanism-kpis', SectionPage::class)
        ->defaults('title', 'Grievance Mechanism KPIs')
        ->defaults('group', 'Indicator Categories')
        ->defaults('description', '26 indicators covering awareness, timeliness, inclusion, SEA/SH, and satisfaction.')
        ->name('categories.grm');

    Route::get('/gender-social-inclusion', SectionPage::class)
        ->defaults('title', 'Gender & Social Inclusion')
        ->defaults('group', 'Indicator Categories')
        ->defaults('description', '28 indicators covering women\'s participation, disability inclusion, and empowerment.')
        ->name('categories.gsi');
});

Route::get('/impact-indicators', SectionPage::class)
    ->defaults('title', 'Impact Indicators')
    ->defaults('group', 'Indicator Categories')
    ->defaults('description', '13 impact dimensions across baseline (2025), midline (2027), and endline (2029).')
    ->name('impact-indicators');

Route::prefix('project-data')->group(function () {
    Route::get('/river-basin', SectionPage::class)
        ->defaults('title', 'River Basin')
        ->defaults('group', 'Project Data')
        ->defaults('description', 'Scheme register by State, Model 1 (RBDA), Model 2 (State), and Scheme name.')
        ->name('project-data.river-basin');

    Route::get('/procurement', SectionPage::class)
        ->defaults('title', 'Procurement')
        ->defaults('group', 'Project Data')
        ->defaults('description', 'Procurement plan register, milestones, and STEP export import.')
        ->name('project-data.procurement');

    Route::get('/awpb', SectionPage::class)
        ->defaults('title', 'Annual Work Plan & Budget')
        ->defaults('group', 'Project Data')
        ->defaults('description', 'Activities with quarterly phasing, expenditure, and absorption.')
        ->name('project-data.awpb');
});

Route::get('/reports', SectionPage::class)
    ->defaults('title', 'Reports')
    ->defaults('group', 'Reporting')
    ->defaults('description', 'Quarterly, annual, and category reports from approved data.')
    ->name('reports');

Route::prefix('admin')->group(function () {
    Route::get('/users', UsersManager::class)->name('admin.users');

    Route::get('/master-data', MasterDataManager::class)->name('admin.master-data');

    Route::get('/audit-log', AuditLogViewer::class)->name('admin.audit-log');

    Route::get('/integrations', SectionPage::class)
        ->defaults('title', 'Integrations')
        ->defaults('group', 'Administration')
        ->defaults('description', 'Source system connections, schedules, and sync logs.')
        ->name('admin.integrations');

    Route::get('/data-backup', SectionPage::class)
        ->defaults('title', 'Data & Backup')
        ->defaults('group', 'Administration')
        ->defaults('description', 'Categories, audit log, and backup status.')
        ->name('admin.data-backup');
});
