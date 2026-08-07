<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation;

/**
 * Overhead Expense Calculator
 *
 * Calculates business overhead expenses and insurance needs for business continuity.
 * Determines key person insurance requirements and overhead expense coverage.
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class OverheadExpenseCalculator
{
    /**
     * Calculate overhead expenses and insurance needs
     */
    public function calculateOverheadExpenses(array $businessData): array
    {
        $expenses = $businessData['expenses'] ?? [];
        $employees = $businessData['employees'] ?? [];
        $keyPersons = $businessData['key_persons'] ?? [];

        // Calculate total annual overhead
        $totalAnnualOverhead = $this->calculateTotalOverhead($expenses);

        // Calculate key person insurance needs
        $keyPersonInsurance = $this->calculateKeyPersonInsurance($keyPersons, $totalAnnualOverhead);

        // Calculate overhead expense insurance
        $overheadInsurance = $this->calculateOverheadInsurance($totalAnnualOverhead, $employees);

        return [
            'total_annual_overhead' => $totalAnnualOverhead,
            'key_person_insurance' => $keyPersonInsurance,
            'overhead_expense_insurance' => $overheadInsurance,
            'total_insurance_recommended' => $keyPersonInsurance['total_coverage'] + $overheadInsurance['coverage_amount'],
            'recommendations' => $this->generateRecommendations($keyPersonInsurance, $overheadInsurance)
        ];
    }

    /**
     * Calculate total annual overhead expenses
     */
    private function calculateTotalOverhead(array $expenses): float
    {
        $total = 0;

        foreach ($expenses as $expense) {
            if (isset($expense['amount']) && isset($expense['frequency'])) {
                $annualAmount = $this->annualizeExpense($expense['amount'], $expense['frequency']);
                $total += $annualAmount;
            }
        }

        return $total;
    }

    /**
     * Annualize expense based on frequency
     */
    private function annualizeExpense(float $amount, string $frequency): float
    {
        return match ($frequency) {
            'monthly' => $amount * 12,
            'quarterly' => $amount * 4,
            'weekly' => $amount * 52,
            'daily' => $amount * 365,
            'annual', 'yearly' => $amount,
            default => $amount // Assume annual if unknown
        };
    }

    /**
     * Calculate key person insurance needs
     */
    private function calculateKeyPersonInsurance(array $keyPersons, float $totalOverhead): array
    {
        $totalCoverage = 0;
        $personDetails = [];

        foreach ($keyPersons as $person) {
            $annualValue = $person['annual_value'] ?? ($totalOverhead * 0.3); // Assume 30% of overhead
            $coverageYears = $person['coverage_years'] ?? 3;
            $coverageAmount = $annualValue * $coverageYears;

            $personDetails[] = [
                'name' => $person['name'] ?? 'Unknown',
                'annual_value' => $annualValue,
                'coverage_years' => $coverageYears,
                'coverage_amount' => $coverageAmount
            ];

            $totalCoverage += $coverageAmount;
        }

        return [
            'persons' => $personDetails,
            'total_coverage' => $totalCoverage,
            'estimated_annual_premium' => $totalCoverage * 0.002 // $2 per $1000 coverage
        ];
    }

    /**
     * Calculate overhead expense insurance
     */
    private function calculateOverheadInsurance(float $totalOverhead, array $employees): array
    {
        // Overhead insurance typically covers 12-24 months of expenses
        $coverageMonths = 18;
        $coverageAmount = ($totalOverhead / 12) * $coverageMonths;

        // Adjust for number of employees
        $employeeCount = count($employees);
        if ($employeeCount > 10) {
            $coverageAmount *= 1.2; // Increase for larger businesses
        }

        return [
            'coverage_months' => $coverageMonths,
            'coverage_amount' => $coverageAmount,
            'estimated_annual_premium' => $coverageAmount * 0.005 // $5 per $1000 coverage
        ];
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(array $keyPersonInsurance, array $overheadInsurance): array
    {
        $recommendations = [];

        if ($keyPersonInsurance['total_coverage'] > 0) {
            $recommendations[] = 'Key person insurance recommended to protect against loss of critical personnel';
        }

        if ($overheadInsurance['coverage_amount'] > 0) {
            $recommendations[] = 'Overhead expense insurance recommended for business continuity during disability';
        }

        $totalInsurance = $keyPersonInsurance['total_coverage'] + $overheadInsurance['coverage_amount'];
        if ($totalInsurance > 1000000) {
            $recommendations[] = 'High insurance requirements - consider phased implementation';
        }

        $recommendations[] = 'Review insurance needs annually as business expenses change';
        $recommendations[] = 'Consider combining key person and overhead policies for cost efficiency';

        return $recommendations;
    }
}