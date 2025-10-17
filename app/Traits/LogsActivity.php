<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Support\Str;

trait LogsActivity 
{
    protected $originalState;

    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logModelEvent($model, 'create');
        });

        static::updating(function ($model) {
            $model->logOriginalState();
        });

        static::updated(function ($model) {
            if ($model->isDirty()) {
                static::logModelEvent($model, 'update');
            }
        });

        static::deleted(function ($model) {
            static::logModelEvent($model, 'delete');
        });
    }

    protected function logOriginalState()
    {
        $this->originalState = $this->getOriginal();
    }

    protected static function logModelEvent($model, $action)
    {
        try {
            $modelName = class_basename($model);
            $description = '';

            switch ($modelName) {
                case 'Obligation':
                    $description = static::getObligationDescription($model, $action);
                    break;
                case 'PurchaseOrder':
                    $description = static::getPurchaseOrderDescription($model, $action);
                    break;
                case 'Disbursement':
                    $description = static::getDisbursementDescription($model, $action);
                    break;
                case 'ObligationAdjustment':
                    $description = static::getObligationAdjustmentDescription($model, $action);
                    break;
                case 'OfficeAllotmentClass':
                    $description = static::getOfficeAllotmentClassDescription($model, $action);
                    break;
                case 'Appropriation':
                    $description = static::getAppropriationDescription($model, $action);
                    break;
                case 'Realignment':
                    $description = static::getRealignmentDescription($model, $action);
                    break;
                case 'Supplemental':
                    $description = static::getSupplementalDescription($model, $action);
                    break;
                case 'AccountCode':
                    $description = static::getAccountCodeDescription($model, $action);
                    break;
                case 'AllotmentClass':
                    $description = static::getAllotmentClassDescription($model, $action);
                    break;
                case 'Fund':
                    $description = static::getFundDescription($model, $action);
                    break;
                case 'FundSource':
                    $description = static::getFundSourceDescription($model, $action);
                    break;
                case 'Office':
                    $description = static::getOfficeDescription($model, $action);
                    break;
                case 'Program':
                    $description = static::getProgramDescription($model, $action);
                    break;
                case 'Sector':
                    $description = static::getSectorDescription($model, $action);
                    break;
                case 'User':
                    $description = static::getUserDescription($model, $action);
                    break;
                case 'Employee':
                    $description = static::getEmployeeDescription($model, $action);
                    break;
                default:
                    $description = static::getDefaultDescription($model, $action);
            }

            ActivityLogger::log($description, $action, null);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error logging activity: ' . $e->getMessage());
        }
    }

    protected static function getObligationDescription($model, $action)
    {
        try {
            // Eager load the relationships
            $model->load([
                'officeAllotmentClass.offices', 
                'officeAllotmentClass.allotmentClass',
                'obligationAmounts.appropriation'
            ]);
            
            // Get office and class details
            $office = optional($model->officeAllotmentClass)->offices->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->officeAllotmentClass)->allotmentClass->class ?? 'Unknown Class';

            // Get account codes and descriptions
            $accountDetails = $model->obligationAmounts->map(function($amount) {
                $code = optional($amount->appropriation)->account_code ?? 'Unknown Code';
                $desc = optional($amount->appropriation)->description ?? 'Unknown Description';
                return "{$code} - {$desc}";
            })->implode(', ');

            switch ($action) {
                case 'create':
                    return "Created new Obligation (OBR# {$model->obr_no}) for {$office} - {$allotmentClass} under Account Code(s): {$accountDetails}";
                case 'update':
                    return "Updated Obligation (OBR# {$model->obr_no}) for {$office} - {$allotmentClass} under Account Code(s): {$accountDetails}";
                case 'delete':
                    return "Deleted Obligation (OBR# {$model->obr_no}) for {$office} - {$allotmentClass} under Account Code(s): {$accountDetails}";
                default:
                    return "Obligation (OBR# {$model->obr_no}) action: {$action}";
            }
        } catch (\Exception $e) {
            return "Obligation action: {$action}";
        }
    }

    protected static function getPurchaseOrderDescription($model, $action)
    {
        try {
            $model->load([
                'obligation.officeAllotmentClass.offices', 
                'obligation.officeAllotmentClass.allotmentClass',
                'obligationAmount.appropriation'
            ]);
            
            $office = optional($model->obligation->officeAllotmentClass)->offices->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->obligation->officeAllotmentClass)->allotmentClass->class ?? 'Unknown Class';
            $accountCode = optional($model->obligationAmount->appropriation)->account_code ?? 'Unknown Code';
            $description = optional($model->obligationAmount->appropriation)->description ?? 'Unknown Description';

            switch ($action) {
                case 'create':
                    return "Created new Purchase Order (PO# {$model->po_number}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                case 'update':
                    return "Updated Purchase Order (PO# {$model->po_number}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                case 'delete':
                    return "Deleted Purchase Order (PO# {$model->po_number}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                default:
                    return "Purchase Order (PO# {$model->po_number}) action: {$action}";
            }
        } catch (\Exception $e) {
            return "Purchase Order action: {$action}";
        }
    }

    protected static function getDisbursementDescription($model, $action)
    {
        try {
            $model->load([
                'obligation.officeAllotmentClass.offices', 
                'obligation.officeAllotmentClass.allotmentClass',
                'obligationAmount.appropriation'
            ]);
            
            $office = optional($model->obligation->officeAllotmentClass)->offices->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->obligation->officeAllotmentClass)->allotmentClass->class ?? 'Unknown Class';
            $accountCode = optional($model->obligationAmount->appropriation)->account_code ?? 'Unknown Code';
            $description = optional($model->obligationAmount->appropriation)->description ?? 'Unknown Description';

            switch ($action) {
                case 'create':
                    return "Created new Disbursement (DV# {$model->dv_no}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                case 'update':
                    return "Updated Disbursement (DV# {$model->dv_no}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                case 'delete':
                    return "Deleted Disbursement (DV# {$model->dv_no}) for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";
                default:
                    return "Disbursement (DV# {$model->dv_no}) action: {$action}";
            }
        } catch (\Exception $e) {
            return "Disbursement action: {$action}";
        }
    }

    protected static function getObligationAdjustmentDescription($model, $action)
    {
        try {
            $model->load([
                'obligation.officeAllotmentClass.offices', 
                'obligation.officeAllotmentClass.allotmentClass',
                'obligationAmount.appropriation',
                'obligation.obligationAmounts'
            ]);
            
            // Check if this adjustment has already been logged
            static $loggedAdjustments = [];
            if (in_array($model->id, $loggedAdjustments) && $action === 'create') {
                return ''; // Skip duplicate adjustment logs
            }
            $loggedAdjustments[] = $model->id;
            
            $office = optional($model->obligation->officeAllotmentClass)->offices->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->obligation->officeAllotmentClass)->allotmentClass->class ?? 'Unknown Class';
            $accountCode = optional($model->obligationAmount->appropriation)->account_code ?? 'Unknown Code';
            $description = optional($model->obligationAmount->appropriation)->description ?? 'Unknown Description';
            
            // Calculate total OBR amount for comparison
            $totalObrAmount = optional($model->obligation)->obligationAmounts->sum('obr_amount') ?? 0;
            $isCancellation = abs($model->adjustment_amount) === abs($totalObrAmount);
            $adjustmentType = $isCancellation ? 'Cancellation' : ($model->adjustment_amount >= 0 ? 'Addition' : 'Deduction');

            switch ($action) {
                case 'create':
                    return "Created new Obligation Adjustment ({$adjustmentType}) for OBR# {$model->obligation->obr_no} under {$office} - {$allotmentClass}, Account Code: {$accountCode} - {$description}";
                case 'update':
                    return "Updated Obligation Adjustment ({$adjustmentType}) for OBR# {$model->obligation->obr_no} under {$office} - {$allotmentClass}, Account Code: {$accountCode} - {$description}";
                case 'delete':
                    return "Deleted Obligation Adjustment ({$adjustmentType}) for OBR# {$model->obligation->obr_no} under {$office} - {$allotmentClass}, Account Code: {$accountCode} - {$description}";
                default:
                    return "Obligation Adjustment for OBR# {$model->obligation->obr_no} action: {$action}";
            }
        } catch (\Exception $e) {
            return "OBR Adjustment action: {$action}";
        }
    }

    protected static function getOfficeAllotmentClassDescription($model, $action)
    {
        try {
            $model->load(['offices', 'allotmentClass']);
            
            $office = optional($model->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->allotmentClass)->class ?? 'Unknown Class';
            $appropriationAmount = number_format($model->appropriation_amount, 2);

            switch ($action) {
                case 'create':
                    return "Created new {$allotmentClass} under {$office}";
                case 'update':
                    return "Updated {$allotmentClass} under {$office}";
                case 'delete':
                    return "Deleted {$allotmentClass} under {$office}";
                default:
                    return "Office Allotment Class action: {$action}";
            }
        } catch (\Exception $e) {
            return "Office Allotment Class action: {$action}";
        }
    }

    protected static function getAppropriationDescription($model, $action)
    {
        try {
            $model->load([
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass'
            ]);
            
            $accountCode = $model->account_code ?? 'Unknown Code';
            $description = $model->description ?? 'Unknown Description';

            $office = optional($model->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->officeAllotmentClass->allotmentClass)->class ?? 'Unknown Class';
            $officeDetails = "{$office} - {$allotmentClass}";

            switch ($action) {
                case 'create':
                    return "Created new Account {$accountCode} - {$description} under {$officeDetails}";
                case 'update':
                    return "Updated Account {$accountCode} - {$description} under {$officeDetails}";
                case 'delete':
                    return "Deleted Account {$accountCode} - {$description} under {$officeDetails}";
                default:
                    return "Account {$accountCode} - {$description} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Account action: {$action}";
        }
    }

    protected static function getRealignmentDescription($model, $action)
    {
        try {
            $model->load([
                'appropriation',
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass'
            ]);

            // Get office and allotment class details
            $office = optional($model->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->officeAllotmentClass->allotmentClass)->class ?? 'Unknown Class';
            $officeDetails = "{$office} - {$allotmentClass}";

            // Get realignment information
            $realignmentNo = $model->realignment_no ?? 'Unknown Realignment No.';
            $type = $model->type ?? 'Unknown Type';

            // Get appropriation details
            $accountCode = optional($model->appropriation)->account_code ?? 'Unknown Code';
            $description = optional($model->appropriation)->description ?? 'Unknown Description';
            
            $details = "{$accountCode} - {$description} ";

            switch ($action) {
                case 'create':
                    return "Created new Realignment No. {$realignmentNo} ({$type}) under {$officeDetails}: {$details}";
                case 'update':
                    return "Updated Realignment No. {$realignmentNo} ({$type}) under {$officeDetails}: {$details}";
                case 'delete':
                    return "Deleted Realignment No. {$realignmentNo} ({$type}) under {$officeDetails}: {$details}";
                default:
                    return "Realignment No. {$realignmentNo} ({$type}) under {$officeDetails} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getRealignmentDescription: ' . $e->getMessage());
            return "Realignment action: {$action}";
        }
    }

    protected static function getSupplementalDescription($model, $action)
    {
        try {
            $model->load([
                'appropriation',
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass'
            ]);

            // Get office and allotment class details
            $office = optional($model->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->officeAllotmentClass->allotmentClass)->class ?? 'Unknown Class';
            $officeDetails = "{$office} - {$allotmentClass}";

            // Get supplemental information
            $supplementalNo = $model->supplemental_no ?? 'Unknown Supplemental No.';
            $type = $model->type ?? 'Unknown Type';

            // Get appropriation details
            $accountCode = optional($model->appropriation)->account_code ?? 'Unknown Code';
            $description = optional($model->appropriation)->description ?? 'Unknown Description';
            
            $details = "{$accountCode} - {$description}";

            switch ($action) {
                case 'create':
                    return "Created new Supplemental No. {$supplementalNo} ({$type}) under {$officeDetails}: {$details}";
                case 'update':
                    return "Updated Supplemental No. {$supplementalNo} ({$type}) under {$officeDetails}: {$details}";
                case 'delete':
                    return "Deleted Supplemental No. {$supplementalNo} ({$type}) under {$officeDetails}: {$details}";
                default:
                    return "Supplemental No. {$supplementalNo} under {$officeDetails} ({$type}) action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getSupplementalDescription: ' . $e->getMessage());
            return "Supplemental action: {$action}";
        }
    }

    protected static function getAccountCodeDescription($model, $action)
    {
        try {
            $model->load(['allotmentClass']);
            $class = optional($model->allotmentClass)->description ?? 'Unknown Class';
            
            switch ($action) {
                case 'create':
                    return "Created new Account Code {$model->code} - {$model->description} under {$class}";
                case 'update':
                    return "Updated Account Code {$model->code} - {$model->description} under {$class}";
                case 'delete':
                    return "Deleted Account Code {$model->code} - {$model->description} under {$class}";
                default:
                    return "Account Code {$model->code} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Account Code action: {$action}";
        }
    }

    protected static function getAllotmentClassDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Allotment Class: {$model->class} - {$model->description} ({$model->category})";
                case 'update':
                    return "Updated Allotment Class: {$model->class} - {$model->description} ({$model->category})";
                case 'delete':
                    return "Deleted Allotment Class: {$model->class} - {$model->description} ({$model->category})";
                default:
                    return "Allotment Class {$model->class} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Allotment Class action: {$action}";
        }
    }

    protected static function getFundDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Fund: {$model->fund} ({$model->fund_type}) - Code: {$model->fund_code}";
                case 'update':
                    return "Updated Fund: {$model->fund} ({$model->fund_type}) - Code: {$model->fund_code}";
                case 'delete':
                    return "Deleted Fund: {$model->fund} ({$model->fund_type}) - Code: {$model->fund_code}";
                default:
                    return "Fund {$model->fund} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Fund action: {$action}";
        }
    }

    protected static function getFundSourceDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Fund Source: {$model->source} (Category: {$model->category})";
                case 'update':
                    return "Updated Fund Source: {$model->source} (Category: {$model->category})";
                case 'delete':
                    return "Deleted Fund Source: {$model->source} (Category: {$model->category})";
                default:
                    return "Fund Source {$model->source} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Fund Source action: {$action}";
        }
    }

    protected static function getOfficeDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Office: {$model->office_name} ({$model->office_abbreviation})";
                case 'update':
                    return "Updated Office: {$model->office_name} ({$model->office_abbreviation})";
                case 'delete':
                    return "Deleted Office: {$model->office_name} ({$model->office_abbreviation})";
                default:
                    return "Office {$model->office_abbreviation} action: {$action}";
            }
        } catch (\Exception $e) {
            return "Office action: {$action}";
        }
    }

    protected static function getProgramDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Program: {$model->program}";
                case 'update':
                    return "Updated Program: {$model->program}";
                case 'delete':
                    return "Deleted Program: {$model->program}";
                default:
                    return "Program action: {$action}";
            }
        } catch (\Exception $e) {
            return "Program action: {$action}";
        }
    }

    protected static function getSectorDescription($model, $action)
    {
        try {
            switch ($action) {
                case 'create':
                    return "Created new Sector: {$model->sector} (Code: {$model->sector_code})";
                case 'update':
                    return "Updated Sector: {$model->sector} (Code: {$model->sector_code})";
                case 'delete':
                    return "Deleted Sector: {$model->sector} (Code: {$model->sector_code})";
                default:
                    return "Sector action: {$action}";
            }
        } catch (\Exception $e) {
            return "Sector action: {$action}";
        }
    }

    protected static function getUserDescription($model, $action)
    {
        try {
            $roles = $model->getRoleNames()->implode(', ');
            $userInfo = "{$model->name} ({$model->username})";
            $userType = ucfirst($model->usertype ?? 'Standard');
            $office = $model->office ?? 'Not Assigned';
            
            switch ($action) {
                case 'create':
                    return "Created new User: {$userInfo} - Type: {$userType}, Office: {$office}" . ($roles ? ", Roles: {$roles}" : "");
                case 'update':
                    return "Updated User: {$userInfo} - Type: {$userType}, Office: {$office}" . ($roles ? ", Roles: {$roles}" : "");
                case 'delete':
                    return "Deleted User: {$userInfo} - Type: {$userType}, Office: {$office}";
                default:
                    return "User {$userInfo} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getUserDescription: ' . $e->getMessage());
            return "User action: {$action}";
        }
    }

    protected static function getEmployeeDescription($model, $action)
    {
        try {
            $employeeInfo = "{$model->name} ({$model->employee_id})";
            $designation = $model->designation ?? 'No Designation';
            $office = $model->office ?? 'Not Assigned';
            
            switch ($action) {
                case 'create':
                    return "Created new Employee: {$employeeInfo} - {$designation} at {$office}";
                case 'update':
                    return "Updated Employee: {$employeeInfo} - {$designation} at {$office}";
                case 'delete':
                    return "Deleted Employee: {$employeeInfo} - {$designation} at {$office}";
                default:
                    return "Employee {$employeeInfo} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getEmployeeDescription: ' . $e->getMessage());
            return "Employee action: {$action}";
        }
    }

    protected static function getDefaultDescription($model, $action)
    {
        $modelName = class_basename($model);
        $modelName = Str::title(Str::snake($modelName, ' '));
        $identifier = $model->id;
        
        switch ($action) {
            case 'create':
                return "Created new {$modelName}: {$identifier}";
            case 'update':
                return "Updated {$modelName}: {$identifier}";
            case 'delete':
                return "Deleted {$modelName}: {$identifier}";
            default:
                return "{$modelName} action: {$action}";
        }
    }
}