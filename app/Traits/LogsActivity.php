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
            $changes = [];

            if ($action === 'update') {
                $changes = $model->getChanges();
                unset($changes['updated_at']);
                if (empty($changes)) {
                    return; // No significant changes to log
                }
            }

            switch ($modelName) {
                case 'Obligation':
                    $description = static::getObligationDescription($model, $action, $changes);
                    break;
                case 'PurchaseOrder':
                    $description = static::getPurchaseOrderDescription($model, $action, $changes);
                    break;
                case 'Disbursement':
                    $description = static::getDisbursementDescription($model, $action, $changes);
                    break;
                case 'ObligationAdjustment':
                    $description = static::getObligationAdjustmentDescription($model, $action, $changes);
                    break;
                case 'ObligationAmount':
                    $description = static::getObligationAmountDescription($model, $action, $changes);
                    break;
                case 'OfficeAllotmentClass':
                    $description = static::getOfficeAllotmentClassDescription($model, $action, $changes);
                    break;
                case 'Appropriation':
                    $description = static::getAppropriationDescription($model, $action, $changes);
                    break;
                case 'Realignment':
                    $description = static::getRealignmentDescription($model, $action, $changes);
                    break;
                case 'Supplemental':
                    $description = static::getSupplementalDescription($model, $action, $changes);
                    break;
                case 'AccountCode':
                    $description = static::getAccountCodeDescription($model, $action, $changes);
                    break;
                case 'AllotmentClass':
                    $description = static::getAllotmentClassDescription($model, $action, $changes);
                    break;
                case 'Fund':
                    $description = static::getFundDescription($model, $action, $changes);
                    break;
                case 'FundSource':
                    $description = static::getFundSourceDescription($model, $action, $changes);
                    break;
                case 'Office':
                    $description = static::getOfficeDescription($model, $action, $changes);
                    break;
                case 'Program':
                    $description = static::getProgramDescription($model, $action, $changes);
                    break;
                case 'Sector':
                    $description = static::getSectorDescription($model, $action, $changes);
                    break;
                case 'User':
                    $description = static::getUserDescription($model, $action, $changes);
                    break;
                case 'Employee':
                    $description = static::getEmployeeDescription($model, $action, $changes);
                    break;
                default:
                    $description = static::getDefaultDescription($model, $action, $changes);
            }

            ActivityLogger::log($description, $action, null);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error logging activity: ' . $e->getMessage());
        }
        
    }

    protected static function formatChanges($model, array $changes): string
{
    $formattedChanges = [];
    
    // Using $model->getOriginal() is the safe way to access the original data 
    // stored by the 'updating' event, regardless of trait property access.
    $originalState = $model->getOriginal() ?? []; 

    foreach ($changes as $attribute => $newValue) {
        // Skip keys that aren't user-facing or helpful for logging
        if (in_array($attribute, ['created_at', 'updated_at', 'deleted_at'])) {
            continue;
        }
        
        // Get the old value from the stored original state
        $oldValue = array_key_exists($attribute, $originalState) ? $originalState[$attribute] : 'N/A';
        
        // Format for readability (using snake_case to Title Case)
        $attributeName = Str::title(Str::replace('_', ' ', Str::snake($attribute)));
        
        // Simple string comparison for log, or use number_format if dealing with currency/numeric fields
        if (is_numeric($oldValue) && is_numeric($newValue)) {
             $oldValue = is_null($oldValue) ? 'null' : number_format($oldValue, 2);
             $newValue = is_null($newValue) ? 'null' : number_format($newValue, 2);
        } elseif (is_bool($oldValue) || is_bool($newValue)) {
            $oldValue = $oldValue ? 'True' : 'False';
            $newValue = $newValue ? 'True' : 'False';
        }

        $formattedChanges[] = "{$attributeName}: '{$oldValue}' to '{$newValue}'";
    }

    return implode('; ', $formattedChanges);
}

    protected static function getObligationDescription($model, $action, $changes = [])
    {
        try {
            // Eager load the relationships
            $model->load([
                'officeAllotmentClass.offices', 
                'officeAllotmentClass.allotmentClass',
                'obligationAmounts.appropriation'
            ]);

            // Get office and class details
            $office = optional($model->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->officeAllotmentClass->allotmentClass)->class ?? 'Unknown Class';

            // Get account codes and descriptions (NO AMOUNT INCLUDED, as requested)
            $accountDetails = $model->obligationAmounts->map(function($amount) {
                $code = optional($amount->appropriation)->account_code ?? 'Unknown Code';
                $desc = optional($amount->appropriation)->description ?? 'Unknown Description';
                return "{$code} - {$desc}";
            })->implode(', ');
            
            $baseDetails = "OBR# {$model->obr_no} for {$office} - {$allotmentClass} under Account Code(s): {$accountDetails}";

            switch ($action) {
                case 'create':
                    return "Created new Obligation ({$baseDetails})";
                    
                case 'update':
                    $baseDescription = "Updated Obligation ({$baseDetails}).";
                    
                    // 1. Log actual parent attribute changes (e.g., obr_no, date, etc.)
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    
                    // 2. FALLBACK: If $changes is empty, assume related models (like ObligationAmount) were updated.
                    // This logs a general message using the current account codes.
                    if ($model->obligationAmounts->isNotEmpty()) {
                        return "{$baseDescription} Obligation amount details were modified. Account codes: [{$accountDetails}]";
                    }
                    
                    // 3. Final Fallback (If no changes at all)
                    return $baseDescription;

                case 'delete':
                    return "Deleted Obligation ({$baseDetails})";
                    
                default:
                    return "Obligation ({$baseDetails}) action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getObligationDescription: ' . $e->getMessage());
            return "Obligation action: {$action}";
        }
    }

    protected static function getObligationAmountDescription($model, $action, $changes = [])
{
    try {
        // Only handle update actions
        if ($action !== 'update') {
            return null;
        }

        // Eager load relationships
        $model->load([
            'obligation.officeAllotmentClass.offices',
            'obligation.officeAllotmentClass.allotmentClass',
            'appropriation'
        ]);

        $obligation = $model->obligation;
        
        // Get Obligation details
        $obrNo = $obligation->obr_no ?? 'Unknown OBR';
        $obrDate = $obligation->obr_date ?? 'Unknown Date';
        $obrType = $obligation->obr_type ?? 'Unknown Type';
        $office = optional($obligation->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office';
        $allotmentClass = optional($obligation->officeAllotmentClass->allotmentClass)->class ?? 'Unknown Class';
        $particulars = $obligation->particulars ?? 'No particulars';
        
        // Get account code details
        $accountCode = optional($model->appropriation)->account_code ?? 'Unknown Code';
        $description = optional($model->appropriation)->description ?? 'Unknown Description';
        
        // Build the base obligation details
        $obligationDetails = "OBR# {$obrNo} dated {$obrDate} ({$obrType}) for {$office} - {$allotmentClass}";
        
        // Track changes
        $changedFields = [];
        
        if (isset($changes['obr_amount'])) {
            $oldAmount = $model->getOriginal('obr_amount') ?? 0;
            $newAmount = $changes['obr_amount'];
            $changedFields[] = "Amount: ₱" . number_format($oldAmount, 2) . " to ₱" . number_format($newAmount, 2);
        }
        
        if (isset($changes['account_code'])) {
            $oldCode = $model->getOriginal('account_code') ?? 'N/A';
            $newCode = $changes['account_code'];
            $changedFields[] = "Account Code: {$oldCode} to {$newCode}";
        }
        
        if (!empty($changedFields)) {
            $changeLog = implode('; ', $changedFields);
            return "Updated obligation amount for {$obligationDetails}, Account Code: {$accountCode} - {$description}. Changes: [{$changeLog}]";
        }
        
        return "Updated obligation amount for {$obligationDetails}, Account Code: {$accountCode} - {$description}";
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error in getObligationAmountDescription: ' . $e->getMessage());
        return "Obligation amount updated";
    }
}

    // --- PurchaseOrder ---
    protected static function getPurchaseOrderDescription($model, $action, $changes = [])
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
            $baseDetails = "PO# {$model->po_number} for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";

            switch ($action) {
                case 'create':
                    return "Created new Purchase Order ({$baseDetails})";
                case 'update':
                    $baseDescription = "Updated Purchase Order ({$baseDetails}).";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted Purchase Order ({$baseDetails})";
                default:
                    return "Purchase Order ({$baseDetails}) action: {$action}";
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error in getPurchaseOrderDescription: ' . $e->getMessage());
            return "Purchase Order action: {$action}";
        }
    }

    // --- Disbursement ---
    protected static function getDisbursementDescription($model, $action, $changes = [])
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
            $baseDetails = "DV# {$model->dv_no} for {$office} - {$allotmentClass} under Account Code: {$accountCode} - {$description}";

            switch ($action) {
                case 'create':
                    return "Created new Disbursement ({$baseDetails})";
                case 'update':
                    $baseDescription = "Updated Disbursement ({$baseDetails}).";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted Disbursement ({$baseDetails})";
                default:
                    return "Disbursement ({$baseDetails}) action: {$action}";
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error in getDisbursementDescription: ' . $e->getMessage());
            return "Disbursement action: {$action}";
        }
    }

    // --- ObligationAdjustment ---
    protected static function getObligationAdjustmentDescription($model, $action, $changes = [])
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
            $baseDetails = "OBR# {$model->obligation->obr_no} under {$office} - {$allotmentClass}, Account Code: {$accountCode} - {$description}";

            switch ($action) {
                case 'create':
                    return "Created new Obligation Adjustment ({$adjustmentType}) for {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated Obligation Adjustment ({$adjustmentType}) for {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted Obligation Adjustment ({$adjustmentType}) for {$baseDetails}";
                default:
                    return "Obligation Adjustment for OBR# {$model->obligation->obr_no} action: {$action}";
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error in getObligationAdjustmentDescription: ' . $e->getMessage());
            return "OBR Adjustment action: {$action}";
        }
    }

    // --- OfficeAllotmentClass ---
    protected static function getOfficeAllotmentClassDescription($model, $action, $changes = [])
    {
        try {
            $model->load(['offices', 'allotmentClass']);
            
            $office = optional($model->offices)->office_abbreviation ?? 'Unknown Office';
            $allotmentClass = optional($model->allotmentClass)->class ?? 'Unknown Class';
            $appropriationAmount = number_format($model->appropriation_amount, 2);
            $baseDetails = "{$allotmentClass} under {$office}";

            switch ($action) {
                case 'create':
                    return "Created new Office Allotment Class: {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated Office Allotment Class: {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted Office Allotment Class: {$baseDetails}";
                default:
                    return "Office Allotment Class action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getOfficeAllotmentClassDescription: ' . $e->getMessage());
            return "Office Allotment Class action: {$action}";
        }
    }

    // --- Appropriation ---
    protected static function getAppropriationDescription($model, $action, $changes = [])
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
            $baseDetails = "Account {$accountCode} - {$description} under {$officeDetails}";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Account {$accountCode} - {$description} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getAppropriationDescription: ' . $e->getMessage());
            return "Account action: {$action}";
        }
    }

    // --- Realignment ---
    protected static function getRealignmentDescription($model, $action, $changes = [])
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
            $details = "{$accountCode} - {$description}";
            
            $baseDetails = "Realignment No. {$realignmentNo} ({$type}) under {$officeDetails}: {$details}";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Realignment No. {$realignmentNo} ({$type}) under {$officeDetails} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getRealignmentDescription: ' . $e->getMessage());
            return "Realignment action: {$action}";
        }
    }
    
    // --- Supplemental ---
    protected static function getSupplementalDescription($model, $action, $changes = [])
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

            $baseDetails = "Supplemental No. {$supplementalNo} ({$type}) under {$officeDetails}: {$details}";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Supplemental No. {$supplementalNo} under {$officeDetails} ({$type}) action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getSupplementalDescription: ' . $e->getMessage());
            return "Supplemental action: {$action}";
        }
    }

    // --- AccountCode ---
    protected static function getAccountCodeDescription($model, $action, $changes = [])
    {
        try {
            $model->load(['allotmentClass']);
            $class = optional($model->allotmentClass)->description ?? 'Unknown Class';
            $baseDetails = "Account Code {$model->code} - {$model->description} under {$class}";
            
            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Account Code {$model->code} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getAccountCodeDescription: ' . $e->getMessage());
            return "Account Code action: {$action}";
        }
    }

    // --- AllotmentClass ---
    protected static function getAllotmentClassDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Allotment Class: {$model->class} - {$model->description} ({$model->category})";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Allotment Class {$model->class} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getAllotmentClassDescription: ' . $e->getMessage());
            return "Allotment Class action: {$action}";
        }
    }

    // --- Fund ---
    protected static function getFundDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Fund: {$model->fund} ({$model->fund_type}) - Code: {$model->fund_code}";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Fund {$model->fund} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFundDescription: ' . $e->getMessage());
            return "Fund action: {$action}";
        }
    }

    // --- FundSource ---
    protected static function getFundSourceDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Fund Source: {$model->source} (Category: {$model->category})";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Fund Source {$model->source} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFundSourceDescription: ' . $e->getMessage());
            return "Fund Source action: {$action}";
        }
    }

    // --- Office ---
    protected static function getOfficeDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Office: {$model->office_name} ({$model->office_abbreviation})";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Office {$model->office_abbreviation} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getOfficeDescription: ' . $e->getMessage());
            return "Office action: {$action}";
        }
    }

    // --- Program ---
    protected static function getProgramDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Program: {$model->program}";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Program action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getProgramDescription: ' . $e->getMessage());
            return "Program action: {$action}";
        }
    }

    // --- Sector ---
    protected static function getSectorDescription($model, $action, $changes = [])
    {
        try {
            $baseDetails = "Sector: {$model->sector} (Code: {$model->sector_code})";

            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Sector action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getSectorDescription: ' . $e->getMessage());
            return "Sector action: {$action}";
        }
    }

    // --- User ---
    protected static function getUserDescription($model, $action, $changes = [])
    {
        try {
            $roles = $model->getRoleNames()->implode(', ');
            $userInfo = "{$model->name} ({$model->username})";
            $userType = ucfirst($model->usertype ?? 'Standard');
            $office = $model->office ?? 'Not Assigned';
            $baseDetails = "User: {$userInfo} - Type: {$userType}, Office: {$office}" . ($roles ? ", Roles: {$roles}" : "");
            
            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
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

    // --- Employee ---
    protected static function getEmployeeDescription($model, $action, $changes = [])
    {
        try {
            $employeeInfo = "{$model->name} ({$model->employee_id})";
            $designation = $model->designation ?? 'No Designation';
            $office = $model->office ?? 'Not Assigned';
            $baseDetails = "Employee: {$employeeInfo} - {$designation} at {$office}";
            
            switch ($action) {
                case 'create':
                    return "Created new {$baseDetails}";
                case 'update':
                    $baseDescription = "Updated {$baseDetails}.";
                    if (!empty($changes)) {
                        $changedFields = static::formatChanges($model, $changes);
                        return "{$baseDescription} Changes: [{$changedFields}]";
                    }
                    return $baseDescription;
                case 'delete':
                    return "Deleted {$baseDetails}";
                default:
                    return "Employee {$employeeInfo} action: {$action}";
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getEmployeeDescription: ' . $e->getMessage());
            return "Employee action: {$action}";
        }
    }

    // --- Default ---
    protected static function getDefaultDescription($model, $action, $changes = [])
    {
        $modelName = class_basename($model);
        $modelName = Str::title(Str::snake($modelName, ' '));
        $identifier = $model->id;
        
        $baseDetails = "{$modelName}: {$identifier}";
        
        switch ($action) {
            case 'create':
                return "Created new {$baseDetails}";
            case 'update':
                $baseDescription = "Updated {$baseDetails}.";
                if (!empty($changes)) {
                    $changedFields = static::formatChanges($model, $changes);
                    return "{$baseDescription} Changes: [{$changedFields}]";
                }
                return $baseDescription;
            case 'delete':
                return "Deleted {$baseDetails}";
            default:
                return "{$modelName} action: {$action}";
        }
    }
}