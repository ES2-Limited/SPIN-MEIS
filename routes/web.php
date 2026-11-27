<?php

use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\CategoriesManager;
use App\Livewire\Admin\MasterDataManager;
use App\Livewire\Admin\UsersManager;
use App\Livewire\CategoryPage;
use App\Livewire\Dashboard;
use App\Livewire\FileUploadManager;
use App\Livewire\IndicatorDetail;
use App\Livewire\RiverBasinRegister;
use App\Livewire\SectionPage;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');

Route::get('/categories/{slug}', CategoryPage::class)->name('categories.show');
Route::get('/indicators/{code}', IndicatorDetail::class)->name('indicators.show');
Route::get('/uploads', FileUploadManager::class)->name('uploads.index');

Route::get('/impact-indicators', SectionPage::class)
    ->defaults('title', 'Impact Indicators')
    ->defaults('group', 'Indicator Categories')
    ->defaults('description', '13 impact dimensions across baseline (2025), midline (2027), and endline (2029).')
    ->name('impact-indicators');

Route::prefix('project-data')->group(function () {
    Route::get('/river-basin', RiverBasinRegister::class)->name('project-data.river-basin');

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

    Route::get('/categories', CategoriesManager::class)->name('admin.categories');

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
