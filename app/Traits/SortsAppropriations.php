<?php

namespace App\Traits;

trait SortsAppropriations
{
    /**
     * Compare account codes for sorting.
     * Account code format: 5-02-01-010
     * Sorting is done from left to right (normal numeric order).
     * 
     * @param string $codeA First account code
     * @param string $codeB Second account code
     * @return int Comparison result
     */
    private function compareAccountCodes($codeA, $codeB)
    {
        // Split the account codes by dash
        $segmentsA = array_map('intval', explode('-', $codeA ?? ''));
        $segmentsB = array_map('intval', explode('-', $codeB ?? ''));
        
        $maxSegments = max(count($segmentsA), count($segmentsB));
        
        // Compare from left to right (normal order)
        for ($i = 0; $i < $maxSegments; $i++) {
            $valA = isset($segmentsA[$i]) ? $segmentsA[$i] : 0;
            $valB = isset($segmentsB[$i]) ? $segmentsB[$i] : 0;
            
            if ($valA !== $valB) {
                return $valA <=> $valB;
            }
        }
        
        return 0; // The codes are equal
    }

    /**
     * Sort appropriations collection with custom logic:
     * 1. Accounts without programs come first
     * 2. Then accounts with programs (grouped by program name alphabetically)
     * 3. Within each group, sort by account code left to right
     * 
     * @param $appropriations Collection of appropriations
     * @return Collection Sorted appropriations
     */
    public function sortAppropriations($appropriations)
    {
        return $appropriations->sort(function ($a, $b) {
            // First, compare by programs (empty/null first)
            $aProgram = empty($a->programs) ? '' : $a->programs;
            $bProgram = empty($b->programs) ? '' : $b->programs;
            
            if ($aProgram === '' && $bProgram !== '') {
                return -1; // a goes first (no program)
            }
            if ($aProgram !== '' && $bProgram === '') {
                return 1; // b goes first (no program)
            }
            
            // If both have same program status, compare programs alphabetically
            if ($aProgram !== $bProgram) {
                return strcmp($aProgram, $bProgram);
            }
            
            // Same program (or both no program), sort by account code left to right
            return $this->compareAccountCodes($a->account_code, $b->account_code);
        })->values();
    }
}
