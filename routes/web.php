    
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
use App\Http\Controllers\PurchaseOrderController;
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
use App\Http\Controllers\QFROController;

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
    Route::resource('appropriations', AppropriationController::class);
    Route::post('/appropriations/import', [AppropriationController::class, 'import'])->name('appropriations.import');
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

    // Obligation Adjustment Routes
    Route::resource('obligation_adjustments', ObligationAdjustmentController::class);

    // Purchase Order Routes
    Route::resource('purchase_orders', PurchaseOrderController::class);
    // Unified Purchase Order module view
    Route::get('purchase-orders/all', [PurchaseOrderController::class, 'all'])->name('purchase_orders.all');

    // Disbursement Routes
    Route::resource('disbursements', DisbursementController::class);
    // Unified Disbursement module view
    Route::get('disbursement/all', [DisbursementController::class, 'all'])->name('disbursements.all');

    // Realignments Routes
    Route::resource('realignments', RealignmentController::class);

    // Supplemental Routes
    Route::resource('supplementals', SupplementalController::class);

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
    // QFRO Report Routes
    Route::get('/summaryaccounts', [AccountsSummaryController::class, 'index'])->name('summaryaccounts.index');
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
