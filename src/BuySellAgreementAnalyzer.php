<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation;

/**
 * Buy-Sell Agreement Analyzer
 *
 * Analyzes buy-sell agreements for business valuation and funding requirements.
 * Determines funding gaps and optimal insurance solutions for business continuity.
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class BuySellAgreementAnalyzer
{
    /**
     * Analyze buy-sell agreement funding requirements
     */
    public function analyzeBuySellAgreement(array $businessData, array $valuationAnalysis): array
    {
        $businessValue = $valuationAnalysis['business_value'];
        $shareholders = $businessData['shareholders'] ?? [];

        // Calculate funding requirements
        $totalFundingNeeded = $businessValue;
        $existingFunding = $this->calculateExistingFunding($businessData);

        $fundingGap = max(0, $totalFundingNeeded - $existingFunding);

        // Determine optimal funding solutions
        $insuranceSolution = $this->calculateInsuranceSolution($fundingGap, $shareholders);
        $installmentSolution = $this->calculateInstallmentSolution($fundingGap);

        return [
            'business_value' => $businessValue,
            'total_funding_needed' => $totalFundingNeeded,
            'existing_funding' => $existingFunding,
            'funding_gap' => $fundingGap,
            'insurance_solution' => $insuranceSolution,
            'installment_solution' => $installmentSolution,
            'recommendations' => $this->generateRecommendations($fundingGap, $insuranceSolution)
        ];
    }

    /**
     * Calculate existing funding sources
     */
    private function calculateExistingFunding(array $businessData): float
    {
        $existingFunding = 0;

        // Add existing life insurance
        if (isset($businessData['insurance_policies'])) {
            foreach ($businessData['insurance_policies'] as $policy) {
                $existingFunding += $policy['death_benefit'] ?? 0;
            }
        }

        // Add cash reserves
        $existingFunding += $businessData['cash_reserves'] ?? 0;

        return $existingFunding;
    }

    /**
     * Calculate insurance funding solution
     */
    private function calculateInsuranceSolution(float $fundingGap, array $shareholders): array
    {
        if ($fundingGap <= 0) {
            return ['required_coverage' => 0, 'annual_premium' => 0, 'feasibility' => 'not_needed'];
        }

        $coverageNeeded = $fundingGap;
        $annualPremium = $this->estimateInsurancePremium($coverageNeeded, $shareholders);

        return [
            'required_coverage' => $coverageNeeded,
            'annual_premium' => $annualPremium,
            'feasibility' => $annualPremium < ($fundingGap * 0.05) ? 'feasible' : 'challenging'
        ];
    }

    /**
     * Calculate installment funding solution
     */
    private function calculateInstallmentSolution(float $fundingGap): array
    {
        if ($fundingGap <= 0) {
            return ['monthly_payment' => 0, 'total_cost' => 0, 'payoff_years' => 0];
        }

        $monthlyPayment = $fundingGap * 0.02; // 2% of funding gap per month
        $totalCost = $fundingGap * 1.5; // 50% interest over time
        $payoffYears = 5; // Assume 5-year payoff

        return [
            'monthly_payment' => $monthlyPayment,
            'total_cost' => $totalCost,
            'payoff_years' => $payoffYears
        ];
    }

    /**
     * Estimate insurance premium
     */
    private function estimateInsurancePremium(float $coverage, array $shareholders): float
    {
        $totalAge = 0;
        foreach ($shareholders as $shareholder) {
            $totalAge += $shareholder['age'] ?? 50;
        }
        $averageAge = count($shareholders) > 0 ? $totalAge / count($shareholders) : 50;

        // Simplified premium calculation: $1 per $1000 coverage per year, adjusted for age
        $baseRate = 0.001;
        $ageMultiplier = 1 + (($averageAge - 40) * 0.02); // 2% increase per year over 40

        return $coverage * $baseRate * $ageMultiplier;
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(float $fundingGap, array $insuranceSolution): array
    {
        $recommendations = [];

        if ($fundingGap > 0) {
            $recommendations[] = 'Funding gap identified - consider implementing buy-sell agreement funding';

            if ($insuranceSolution['feasibility'] === 'feasible') {
                $recommendations[] = 'Life insurance appears feasible for funding the buy-sell agreement';
            } else {
                $recommendations[] = 'Consider installment payments or other funding mechanisms due to premium costs';
            }
        } else {
            $recommendations[] = 'Buy-sell agreement appears adequately funded';
        }

        $recommendations[] = 'Review funding annually as business value changes';
        $recommendations[] = 'Consider disability insurance for business continuity';

        return $recommendations;
    }
}