    
<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\AccountsSummaryController;
use App\Http\Controllers\AppropriationController;
use App\Http\Controllers\ObligationAdjustmentController;
use App\Http\Controllers\OfficeAllotmentClassController;
use App\Http\Controllers\ObligationController;
use App\Http\Controllers\ObligationFileController;
use App\Http\Controllers\RealignmentFileController;
use App\Http\Controllers\SupplementalFileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderFileController;
use App\Http\Controllers\AllotmentClassController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundSourceController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RealignmentController;
use App\Http\Controllers\SupplementalController;
use App\Http\Controllers\SAAOBController;
use App\Http\Controllers\SAAOBFundSectorController;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\RAOController;
use App\Http\Controllers\SAAOBFundSourceController;
use App\Http\Controllers\SAAOBCOController;
use App\Http\Controllers\SAAOBGFCurrentController;
use App\Http\Controllers\SAAOBGFCurrentSummaryController;
use App\Http\Controllers\SAAODBOfficeController;
use App\Http\Controllers\SAAODBAllFundsController;
use App\Http\Controllers\SAAODBGFController;
use App\Http\Controllers\NDDController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth'])->group(function() {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data');
});

Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/accounts/{id}', [DashboardController::class, 'accounts'])->name('dashboard.accounts');

    // User Routes (restricted to Developer and Administrator)
    Route::resource('users', UserController::class);
    //Employee Routes
    Route::get('/employees/check-unique', [EmployeeController::class, 'checkUnique']);
    Route::resource('employees', EmployeeController::class);
    //Account Code Routes
    Route::resource('account_codes', AccountCodeController::class);
    //Office Routes
    Route::resource('offices', OfficeController::class);
    //Office Allotment Class Routes
    Route::resource('office_allotment_classes', OfficeAllotmentClassController::class);
    //Allotment Class Routes
    Route::resource('allotment_classes', AllotmentClassController::class);
    //Fund Routes
    Route::resource('funds', FundController::class);
    //Fund Source Routes
    Route::resource('fund_sources', FundSourceController::class);
    //Sector Routes
    Route::resource('sectors', SectorController::class);
    //Program Routes
    Route::resource('programs', ProgramController::class);
    //Appropriation Routes
    Route::get('appropriations/account-codes', [AppropriationController::class, 'getAccountCodes'])
        ->name('appropriations.accountCodes');

    Route::get('appropriations/allotment-class-info', [AppropriationController::class, 'getAllotmentClassInfo'])
        ->name('appropriations.getAllotmentClassInfo');
    
    Route::get('appropriations/by-office-allotment-class', [AppropriationController::class, 'getByOfficeAllotmentClass'])
        ->name('appropriations.getByOfficeAllotmentClass');
    
    Route::get('appropriations/last-year', [AppropriationController::class, 'getLastYearappropriations'])
        ->name('appropriations.getLastYear');
    
    Route::post('appropriations/store-from-last-year', [AppropriationController::class, 'storeFromLastYear'])
        ->name('appropriations.storeFromLastYear');
    
    Route::post('appropriations/import', [AppropriationController::class, 'import'])
        ->name('appropriations.import');
    
    // Resource route - MUST BE AFTER custom routes
    Route::resource('appropriations', AppropriationController::class);
    //Get Fund
    Route::get('/get-fund/{office_id}', function ($office_id) {
        $office = Office::find($office_id);
        return response()->json([
            'fund' => $office ? $office->fund : '',
            'fpp_code' => $office ? $office->fpp_code : '',
            'responsibility_code' => $office ? $office->responsibility_code : '',
            'sub_office' => $office ? $office->sub_office : '',
            'office_abbreviation' => $office ? $office->office_abbreviation : ''
        ]);
    });
    //Get Office Allotment Classes
    Route::get('/get-continuing-allotment-classes', [OfficeAllotmentClassController::class, 'getContinuingAllotmentClasses']);

    //Obligation Routes
    Route::resource('obligations', ObligationController::class);
    Route::post('/obligations/{obligation}/cancel', [ObligationController::class, 'cancel'])->name('obligations.cancel');
    Route::get('obligations/{obligation}/purchase-order-modal', [ObligationController::class, 'showPurchaseOrderModal'])->name('obligations.purchase_order_modal');
    Route::post('/obligations/{obligation}/store-purchase-order', [ObligationController::class, 'storePurchaseOrder'])->name('obligations.storePurchaseOrder');
    Route::get('obligations/{obligation}/obligation-adjustment-modal', [ObligationController::class, 'showObligationAdjustmentModal'])->name('obligations.obligation_adjustment_modal');
    Route::post('/obligations/{obligation}/store-obligation-adjustment', [ObligationController::class, 'storeObligationAdjustment'])->name('obligations.storeObligationAdjustment');
    Route::get('obligations/{obligation}/disbursement-modal', [ObligationController::class, 'showDisbursementModal'])->name('obligations.disbursement_modal');
    Route::post('/obligations/{obligation}/store-disbursement', [ObligationController::class, 'storeDisbursement'])->name('obligations.storeDisbursement');
    Route::post('/obligations/{obligation}/payment-remarks', [ObligationController::class, 'updatePaymentRemarks'])->name('obligations.updatePaymentRemarks');
    Route::get('/obligations/{obligation}/activity-history', [ObligationController::class, 'activityHistory'])->name('obligations.activityHistory');
    Route::get('/api/obligations/by-office-allotment-class/{classId}', [ObligationController::class, 'getByOfficeAllotmentClass'])->name('obligations.api.byOfficeAllotmentClass');
    Route::get('/api/obligations/by-appropriation/{appropriationId}', [ObligationController::class, 'getByAppropriation'])->name('obligations.api.byAppropriation');
    Route::get('/api/obligations/{obligationId}/details', [ObligationController::class, 'getObligationDetails'])->name('obligations.api.details');
    Route::get('/api/obligations/{obligationId}/amounts', [ObligationController::class, 'getObligationAmounts'])->name('obligations.api.amounts');
    Route::get('/api/obligations/{obligationId}/year', [ObligationController::class, 'getObligationYear'])->name('obligations.api.year');
    Route::get('/api/obligations/existing-obr-numbers', [ObligationController::class, 'getExistingObrNumbers'])->name('obligations.api.existingObrNumbers');

    // Obligation Files Routes
    Route::post('/obligations/{obligation}/files', [ObligationFileController::class, 'store'])->name('obligation_files.store');
    Route::get('/obligations/{obligation}/files', [ObligationFileController::class, 'getFiles'])->name('obligation_files.get');
    Route::get('/obligation-files/{obligationFile}/view', [ObligationFileController::class, 'view'])->name('obligation_files.view');
    Route::get('/obligation-files/{obligationFile}/download', [ObligationFileController::class, 'download'])->name('obligation_files.download');
    Route::get('/obligation-files/{obligationFile}/preview', [ObligationFileController::class, 'preview'])->name('obligation_files.preview');
    Route::put('/obligation-files/{obligationFile}', [ObligationFileController::class, 'update'])->name('obligation_files.update');
    Route::delete('/obligation-files/{obligationFile}', [ObligationFileController::class, 'destroy'])->name('obligation_files.destroy');

    // Obligation Adjustment Routes
    Route::resource('obligation_adjustments', ObligationAdjustmentController::class);

    // Purchase Order Routes
    Route::resource('purchase_orders', PurchaseOrderController::class);
    // Unified Purchase Order module view
    Route::get('purchase-orders/all', [PurchaseOrderController::class, 'all'])->name('purchase_orders.all');
    // API endpoint to get purchase orders by po_number
    Route::get('/api/purchase-orders/by-number/{poNumber}', [PurchaseOrderController::class, 'getByPoNumber'])->name('purchase_orders.api.byNumber');

    // Purchase Order Files Routes
    Route::post('/purchase-order-files/upload', [PurchaseOrderFileController::class, 'store'])->name('purchase_order_files.store');
    Route::get('/purchase-order-files/get', [PurchaseOrderFileController::class, 'getFiles'])->name('purchase_order_files.get');
    Route::get('/purchase-order-files/{purchaseOrderFile}/view', [PurchaseOrderFileController::class, 'view'])->name('purchase_order_files.view');
    Route::get('/purchase-order-files/{purchaseOrderFile}/download', [PurchaseOrderFileController::class, 'download'])->name('purchase_order_files.download');
    Route::get('/purchase-order-files/{purchaseOrderFile}/preview', [PurchaseOrderFileController::class, 'preview'])->name('purchase_order_files.preview');
    Route::put('/purchase-order-files/{purchaseOrderFile}', [PurchaseOrderFileController::class, 'update'])->name('purchase_order_files.update');
    Route::delete('/purchase-order-files/{purchaseOrderFile}', [PurchaseOrderFileController::class, 'destroy'])->name('purchase_order_files.destroy');

    // Disbursement Routes
    Route::resource('disbursements', DisbursementController::class);
    // Unified Disbursement module view
    Route::get('disbursement/all', [DisbursementController::class, 'all'])->name('disbursements.all');
    // Check DV number uniqueness
    Route::post('/api/disbursements/check-dv-number', [DisbursementController::class, 'checkDvNumber'])->name('disbursements.checkDvNumber');
    // Check PO number uniqueness
    Route::post('/api/purchase-orders/check-po-number', [PurchaseOrderController::class, 'checkPoNumber'])->name('purchase_orders.checkPoNumber');

    // Realignments Routes
    Route::resource('realignments', RealignmentController::class);
    // Check realignment deletion date
    Route::post('/api/realignments/check-deletion-date', [RealignmentController::class, 'checkRealignmentDeletionDate'])->name('realignments.checkDeletionDate');

    // Realignment Files Routes
    Route::post('/realignment-files/upload', [RealignmentFileController::class, 'store'])->name('realignment_files.store');
    Route::get('/realignment-files/get', [RealignmentFileController::class, 'getFiles'])->name('realignment_files.get');
    Route::get('/realignment-files/{realignmentFile}/view', [RealignmentFileController::class, 'view'])->name('realignment_files.view');
    Route::get('/realignment-files/{realignmentFile}/download', [RealignmentFileController::class, 'download'])->name('realignment_files.download');
    Route::get('/realignment-files/{realignmentFile}/preview', [RealignmentFileController::class, 'preview'])->name('realignment_files.preview');
    Route::put('/realignment-files/{realignmentFile}', [RealignmentFileController::class, 'update'])->name('realignment_files.update');
    Route::delete('/realignment-files/{realignmentFile}', [RealignmentFileController::class, 'destroy'])->name('realignment_files.destroy');

    // Supplemental Files Routes
    Route::post('/supplemental-files/upload', [SupplementalFileController::class, 'store'])->name('supplemental_files.store');
    Route::get('/supplemental-files/get', [SupplementalFileController::class, 'getFiles'])->name('supplemental_files.get');
    Route::get('/supplemental-files/{supplementalFile}/view', [SupplementalFileController::class, 'view'])->name('supplemental_files.view');
    Route::get('/supplemental-files/{supplementalFile}/download', [SupplementalFileController::class, 'download'])->name('supplemental_files.download');
    Route::get('/supplemental-files/{supplementalFile}/preview', [SupplementalFileController::class, 'preview'])->name('supplemental_files.preview');
    Route::put('/supplemental-files/{supplementalFile}', [SupplementalFileController::class, 'update'])->name('supplemental_files.update');
    Route::delete('/supplemental-files/{supplementalFile}', [SupplementalFileController::class, 'destroy'])->name('supplemental_files.destroy');

    // Supplemental Routes
    Route::resource('supplementals', SupplementalController::class);
    // Check supplemental deletion date
    Route::post('/api/supplementals/check-deletion-date', [SupplementalController::class, 'checkSupplementalDeletionDate'])->name('supplementals.checkDeletionDate');

    // SAAOB by Office-Current Report Routes
    Route::get('/saaob', [SAAOBController::class, 'index'])->name('saaob.index');
    // SAAOB Excel Export
    Route::get('saaob/export-excel', [SAAOBController::class, 'exportExcel'])->name('saaob.exportExcel');
    // SAAOB by Office-Continuing Report Routes
    Route::get('/saaobco', [SAAOBCOController::class, 'index'])->name('saaobco.index');
    // SAAOBCO Excel Export
    Route::get('saaobco/export-excel', [SAAOBCOController::class, 'exportExcel'])->name('saaobco.exportExcel');
    // SAAOB Fund per Sector by Fund per Sector Report Routes
    Route::get('/saaobfundsector', [SAAOBFundSectorController::class, 'index'])->name('saaobfundsector.index');
    // SAAOB Fund per Sector Excel Export
    Route::get('saaobFundSector/export-excel', [SAAOBFundSectorController::class, 'exportExcel'])->name('saaobFundSector.exportExcel');
    // SAAOB All Funds Report Routes
    Route::get('/saaobfundsource', [SAAOBFundSourceController::class, 'index'])->name('saaobfundsource.index');
    // SAAOB Fund per Sector Excel Export
    Route::get('saaobFundSource/export-excel', [SAAOBFundSourceController::class, 'exportExcel'])->name('saaobFundSource.exportExcel');
    // SAAOB GF Current Report Routes
    Route::get('/saaobgfcurrent', [SAAOBGFCurrentController::class, 'index'])->name('saaobgfcurrent.index');
    // SAAOB GF Current Excel Export
    Route::get('saaobGFCurrent/export-excel', [SAAOBGFCurrentController::class, 'exportExcel'])->name('saaobGFCurrent.exportExcel');
    // SAAOB GF Current Summary Report Routes
    Route::get('/saaobgfcurrentsummary', [SAAOBGFCurrentSummaryController::class, 'index'])->name('saaobgfcurrentsummary.index');
    // SAAOB GF Current Excel Export
    Route::get('saaobGFCurrentSummary/export-excel', [SAAOBGFCurrentSummaryController::class, 'exportExcel'])->name('saaobGFCurrentSummary.exportExcel');
    // SAAODB Offices Report Routes
    Route::get('/saaodboffices', [SAAODBOfficeController::class, 'index'])->name('saaodboffice.index');
    // SAAODB Offices Excel Export
    Route::get('saaodb/export-excel', [SAAODBOfficeController::class, 'exportExcel'])->name('saaodb.exportExcel');
    // SAAODB All Funds Report Routes
    Route::get('/saaodballfunds', [SAAODBAllFundsController::class, 'index'])->name('saaodballfunds.index');
    // SAAODB All Funds Excel Export
    Route::get('saaodbAllFunds/export-excel', [SAAODBAllFundsController::class, 'exportExcel'])->name('saaodbAllFunds.exportExcel');
    // SAAODB GF Report Routes
    Route::get('/saaodbgf', [SAAODBGFController::class, 'index'])->name('saaodbgf.index');
    // SAAODB GF Excel Export
    Route::get('saaodbGF/export-excel', [SAAODBGFController::class, 'exportExcel'])->name('saaodbGF.exportExcel');
    // RAO Report Routes
    Route::get('/rao', [RAOController::class, 'index'])->name('rao.index');
    // RAO Excel Export
    Route::get('rao/export-excel', [RAOController::class, 'exportExcel'])->name('rao.exportExcel');
    // Accounts Summary Report Routes
    Route::get('/summaryaccounts', [AccountsSummaryController::class, 'index'])->name('summaryaccounts.index');
    // Accounts Summary Excel Export
    Route::get('summaryaccounts/export-excel', [AccountsSummaryController::class, 'exportExcel'])->name('summaryaccounts.exportExcel');
    // NDD Report Routes
    Route::get('/ndd', [NDDController::class, 'index'])->name('ndd.index');
    // NDD Excel Export
    Route::get('ndd/export-excel', [NDDController::class, 'exportExcel'])->name('ndd.exportExcel');

    // Document Routes (SPA with modals)
    Route::get('documents/search/api', [DocumentController::class, 'search'])->name('documents.search');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/files/{file}/download', [DocumentController::class, 'downloadFile'])->name('documents.downloadFile');
    Route::get('documents/{document}/files/{file}', [DocumentController::class, 'viewFile'])->name('documents.viewFile');
    Route::resource('documents', DocumentController::class, ['except' => ['create', 'edit']]);
});

// useless routes
// Just to demo sidebar dropdown links active states.
Route::get('/buttons/text', function () {
    return view('buttons-showcase.text');
})->middleware(['auth'])->name('buttons.text');

Route::get('/buttons/icon', function () {
    return view('buttons-showcase.icon');
})->middleware(['auth'])->name('buttons.icon');

Route::get('/buttons/text-icon', function () {
    return view('buttons-showcase.text-icon');
})->middleware(['auth'])->name('buttons.text-icon');

require __DIR__ . '/auth.php';
