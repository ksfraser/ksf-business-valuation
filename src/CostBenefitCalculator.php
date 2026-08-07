<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation;

/**
 * Cost-Benefit Calculator
 *
 * Calculates cost-benefit ratios and financial analysis for insurance plans.
 * Performs detailed analysis of premiums vs. benefits, return on investment,
 * and long-term value propositions.
 *
 * Single Responsibility: Calculate and analyze cost-benefit ratios for insurance plans.
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class CostBenefitCalculator
{
    /**
     * Analysis time horizons
     */
    public const HORIZON_5_YEARS = 5;
    public const HORIZON_10_YEARS = 10;
    public const HORIZON_20_YEARS = 20;
    public const HORIZON_LIFETIME = 'lifetime';

    /**
     * Calculate cost-benefit analysis for multiple plans
     *
     * @param array $plans Array of plan data
     * @param array $clientProfile Client financial profile
     * @param array $policyAnalysis Policy analysis results
     * @return array Cost-benefit analysis results
     */
    public function calculateCostBenefits(array $plans, array $clientProfile, array $policyAnalysis): array
    {
        $analysis = [
            'summary' => [],
            'detailed_analysis' => [],
            'roi_projections' => [],
            'break_even_analysis' => [],
            'value_propositions' => []
        ];

        foreach ($plans as $index => $plan) {
            $planId = $plan['id'] ?? 'plan_' . $index;

            $analysis['detailed_analysis'][$planId] = $this->analyzeSinglePlanCostBenefit(
                $plan,
                $clientProfile
            );

            $analysis['roi_projections'][$planId] = $this->calculateROIProjection(
                $plan,
                $clientProfile
            );

            $analysis['break_even_analysis'][$planId] = $this->calculateBreakEven(
                $plan,
                $clientProfile
            );

            $analysis['value_propositions'][$planId] = $this->calculateValueProposition(
                $plan,
                $clientProfile
            );
        }

        $analysis['summary'] = $this->generateSummary($analysis);

        return $analysis;
    }

    /**
     * Analyze cost-benefit for a single plan
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return array Cost-benefit analysis for the plan
     */
    private function analyzeSinglePlanCostBenefit(array $plan, array $clientProfile): array
    {
        $annualPremium = $plan['premiums']['annual'] ?? 0;
        $coverageAmount = $plan['coverage_amount'] ?? 0;

        return [
            'cost_benefit_ratio' => $this->calculateCostBenefitRatio($annualPremium, $coverageAmount),
            'annual_cost_percentage' => $this->calculateAnnualCostPercentage($annualPremium, $clientProfile),
            'lifetime_cost_projection' => $this->calculateLifetimeCostProjection($annualPremium, $clientProfile),
            'coverage_efficiency' => $this->calculateCoverageEfficiency($coverageAmount, $annualPremium),
            'benefit_cost_ratio' => $this->calculateBenefitCostRatio($plan, $clientProfile)
        ];
    }

    /**
     * Calculate cost-benefit ratio
     *
     * @param float $annualCost Annual premium cost
     * @param float $coverageAmount Coverage amount
     * @return float Cost-benefit ratio
     */
    private function calculateCostBenefitRatio(float $annualCost, float $coverageAmount): float
    {
        if ($coverageAmount <= 0) {
            return 0.0;
        }

        // Cost as percentage of coverage (lower is better)
        return ($annualCost / $coverageAmount) * 100;
    }

    /**
     * Calculate annual cost as percentage of income
     *
     * @param float $annualCost Annual premium cost
     * @param array $clientProfile Client profile
     * @return float Cost as percentage of income
     */
    private function calculateAnnualCostPercentage(float $annualCost, array $clientProfile): float
    {
        $annualIncome = $clientProfile['annual_income'] ?? 0;

        if ($annualIncome <= 0) {
            return 0.0;
        }

        return ($annualCost / $annualIncome) * 100;
    }

    /**
     * Calculate lifetime cost projection
     *
     * @param float $annualCost Annual premium cost
     * @param array $clientProfile Client profile
     * @return array Lifetime cost projections
     */
    private function calculateLifetimeCostProjection(float $annualCost, array $clientProfile): array
    {
        $currentAge = $clientProfile['age'] ?? 30;
        $lifeExpectancy = $clientProfile['life_expectancy'] ?? 80;

        $yearsRemaining = max(0, $lifeExpectancy - $currentAge);

        return [
            'years_remaining' => $yearsRemaining,
            'total_lifetime_cost' => $annualCost * $yearsRemaining,
            'cost_per_year_remaining' => $yearsRemaining > 0 ? $annualCost : 0
        ];
    }

    /**
     * Calculate coverage efficiency
     *
     * @param float $coverageAmount Coverage amount
     * @param float $annualCost Annual cost
     * @return float Coverage efficiency score
     */
    private function calculateCoverageEfficiency(float $coverageAmount, float $annualCost): float
    {
        if ($annualCost <= 0) {
            return 0.0;
        }

        // Coverage per dollar spent annually (higher is better)
        return $coverageAmount / $annualCost;
    }

    /**
     * Calculate benefit-cost ratio
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return float Benefit-cost ratio
     */
    private function calculateBenefitCostRatio(array $plan, array $clientProfile): float
    {
        $annualCost = $plan['premiums']['annual'] ?? 0;
        $requiredCoverage = $clientProfile['required_coverage']['total'] ?? 0;
        $providedCoverage = $plan['coverage_amount'] ?? 0;

        if ($annualCost <= 0) {
            return 0.0;
        }

        // Ratio of required coverage provided vs. cost (higher is better)
        $coverageRatio = min(1.0, $providedCoverage / $requiredCoverage);
        return $coverageRatio / ($annualCost / 1000); // Normalized per $1000 cost
    }

    /**
     * Calculate ROI projection for a plan
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return array ROI projections for different time horizons
     */
    private function calculateROIProjection(array $plan, array $clientProfile): array
    {
        $annualPremium = $plan['premiums']['annual'] ?? 0;
        $coverageAmount = $plan['coverage_amount'] ?? 0;

        $projections = [];

        foreach ([self::HORIZON_5_YEARS, self::HORIZON_10_YEARS, self::HORIZON_20_YEARS] as $years) {
            $totalCost = $annualPremium * $years;

            // Simplified ROI calculation - in reality this would be more complex
            // considering probability of claims, investment returns, etc.
            $expectedValue = $this->calculateExpectedValue($plan, $years, $clientProfile);
            $roi = $expectedValue > 0 ? (($expectedValue - $totalCost) / $totalCost) * 100 : 0;

            $projections[$years . '_years'] = [
                'total_cost' => $totalCost,
                'expected_value' => $expectedValue,
                'roi_percentage' => $roi,
                'break_even_year' => $this->calculateBreakEvenYear($annualPremium, $plan, $clientProfile)
            ];
        }

        return $projections;
    }

    /**
     * Calculate expected value of a plan
     *
     * @param array $plan Plan data
     * @param int $years Time horizon
     * @param array $clientProfile Client profile
     * @return float Expected value
     */
    private function calculateExpectedValue(array $plan, int $years, array $clientProfile): float
    {
        // Simplified calculation - in practice this would use actuarial tables
        // and probability calculations
        $coverageAmount = $plan['coverage_amount'] ?? 0;
        $claimProbability = $this->estimateClaimProbability($plan, $clientProfile);

        // Expected value = Coverage Amount × Probability of Claim × Survival Factor
        $survivalFactor = $this->calculateSurvivalFactor($years, $clientProfile);

        return $coverageAmount * $claimProbability * $survivalFactor;
    }

    /**
     * Estimate claim probability
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return float Claim probability (0-1)
     */
    private function estimateClaimProbability(array $plan, array $clientProfile): float
    {
        // Simplified estimation based on coverage type and client health
        $baseProbability = 0.05; // 5% base probability

        // Adjust based on coverage types
        if (in_array('critical_illness', $plan['coverage_types'] ?? [])) {
            $baseProbability += 0.02;
        }

        if (in_array('disability', $plan['coverage_types'] ?? [])) {
            $baseProbability += 0.03;
        }

        // Adjust based on client health
        $healthFactor = $clientProfile['health_score'] ?? 0.5; // 0-1 scale
        $baseProbability *= (1 + (0.5 - $healthFactor)); // Worse health = higher probability

        return min(1.0, max(0.0, $baseProbability));
    }

    /**
     * Calculate survival factor
     *
     * @param int $years Years to project
     * @param array $clientProfile Client profile
     * @return float Survival factor (0-1)
     */
    private function calculateSurvivalFactor(int $years, array $clientProfile): float
    {
        $currentAge = $clientProfile['age'] ?? 30;
        $futureAge = $currentAge + $years;

        // Simplified survival calculation - in practice use actuarial tables
        if ($futureAge < 50) return 0.95;
        if ($futureAge < 70) return 0.85;
        if ($futureAge < 90) return 0.70;
        return 0.50;
    }

    /**
     * Calculate break-even analysis
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return array Break-even analysis
     */
    private function calculateBreakEven(array $plan, array $clientProfile): array
    {
        $annualPremium = $plan['premiums']['annual'] ?? 0;
        $coverageAmount = $plan['coverage_amount'] ?? 0;

        $breakEvenYear = $this->calculateBreakEvenYear($annualPremium, $plan, $clientProfile);

        return [
            'break_even_year' => $breakEvenYear,
            'cumulative_cost_at_break_even' => $annualPremium * $breakEvenYear,
            'coverage_amount' => $coverageAmount,
            'cost_recovery_percentage' => $coverageAmount > 0 ? ($annualPremium * $breakEvenYear / $coverageAmount) * 100 : 0
        ];
    }

    /**
     * Calculate break-even year
     *
     * @param float $annualPremium Annual premium
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return float Break-even year
     */
    private function calculateBreakEvenYear(float $annualPremium, array $plan, array $clientProfile): float
    {
        if ($annualPremium <= 0) {
            return 0.0;
        }

        $expectedValuePerYear = $this->calculateExpectedValue($plan, 1, $clientProfile);

        if ($expectedValuePerYear <= 0) {
            return 99.0; // Never breaks even
        }

        return $annualPremium / $expectedValuePerYear;
    }

    /**
     * Calculate value proposition for a plan
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return array Value proposition analysis
     */
    private function calculateValueProposition(array $plan, array $clientProfile): array
    {
        $costBenefitRatio = $this->calculateCostBenefitRatio(
            $plan['premiums']['annual'] ?? 0,
            $plan['coverage_amount'] ?? 0
        );

        $affordability = $this->calculateAnnualCostPercentage(
            $plan['premiums']['annual'] ?? 0,
            $clientProfile
        );

        // Calculate value score (higher is better)
        $valueScore = 0;

        // Cost efficiency (lower cost-benefit ratio is better)
        if ($costBenefitRatio < 1.0) $valueScore += 30;
        elseif ($costBenefitRatio < 2.0) $valueScore += 20;
        elseif ($costBenefitRatio < 5.0) $valueScore += 10;

        // Affordability (lower percentage is better)
        if ($affordability < 1.0) $valueScore += 25;
        elseif ($affordability < 3.0) $valueScore += 20;
        elseif ($affordability < 5.0) $valueScore += 15;

        // Coverage adequacy
        $coverageMatch = $this->calculateCoverageMatch($plan, $clientProfile);
        $valueScore += $coverageMatch * 25;

        // Additional features
        $featureCount = count($plan['features'] ?? []);
        $valueScore += min(20, $featureCount * 5);

        return [
            'value_score' => min(100, $valueScore),
            'cost_efficiency_rating' => $this->getCostEfficiencyRating($costBenefitRatio),
            'affordability_rating' => $this->getAffordabilityRating($affordability),
            'coverage_adequacy' => $coverageMatch * 100,
            'key_value_drivers' => $this->identifyValueDrivers($plan, $clientProfile)
        ];
    }

    /**
     * Calculate coverage match (helper method)
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return float Coverage match (0-1)
     */
    private function calculateCoverageMatch(array $plan, array $clientProfile): float
    {
        $required = $clientProfile['required_coverage']['total'] ?? 0;
        $provided = $plan['coverage_amount'] ?? 0;

        if ($required <= 0) return 1.0;
        return min(1.0, $provided / $required);
    }

    /**
     * Get cost efficiency rating
     *
     * @param float $ratio Cost-benefit ratio
     * @return string Rating description
     */
    private function getCostEfficiencyRating(float $ratio): string
    {
        if ($ratio < 1.0) return 'Excellent';
        if ($ratio < 2.0) return 'Very Good';
        if ($ratio < 5.0) return 'Good';
        if ($ratio < 10.0) return 'Fair';
        return 'Poor';
    }

    /**
     * Get affordability rating
     *
     * @param float $percentage Annual cost percentage
     * @return string Rating description
     */
    private function getAffordabilityRating(float $percentage): string
    {
        if ($percentage < 1.0) return 'Excellent';
        if ($percentage < 3.0) return 'Very Good';
        if ($percentage < 5.0) return 'Good';
        if ($percentage < 8.0) return 'Fair';
        return 'Poor';
    }

    /**
     * Identify key value drivers
     *
     * @param array $plan Plan data
     * @param array $clientProfile Client profile
     * @return array List of value drivers
     */
    private function identifyValueDrivers(array $plan, array $clientProfile): array
    {
        $drivers = [];

        $costBenefitRatio = $this->calculateCostBenefitRatio(
            $plan['premiums']['annual'] ?? 0,
            $plan['coverage_amount'] ?? 0
        );

        if ($costBenefitRatio < 2.0) {
            $drivers[] = 'Cost-effective coverage';
        }

        $affordability = $this->calculateAnnualCostPercentage(
            $plan['premiums']['annual'] ?? 0,
            $clientProfile
        );

        if ($affordability < 3.0) {
            $drivers[] = 'Highly affordable';
        }

        if ($this->calculateCoverageMatch($plan, $clientProfile) > 0.8) {
            $drivers[] = 'Excellent coverage match';
        }

        if (!empty($plan['features'])) {
            $drivers[] = 'Additional valuable features';
        }

        return $drivers;
    }

    /**
     * Generate summary of all plan analyses
     *
     * @param array $analysis Complete analysis data
     * @return array Summary statistics
     */
    private function generateSummary(array $analysis): array
    {
        $planCount = count($analysis['detailed_analysis']);

        // Find best value plan
        $bestValuePlan = null;
        $bestValueScore = 0;

        foreach ($analysis['value_propositions'] as $planId => $valueProp) {
            if ($valueProp['value_score'] > $bestValueScore) {
                $bestValueScore = $valueProp['value_score'];
                $bestValuePlan = $planId;
            }
        }

        return [
            'total_plans_analyzed' => $planCount,
            'best_value_plan' => $bestValuePlan,
            'best_value_score' => $bestValueScore,
            'average_cost_benefit_ratio' => $this->calculateAverageRatio($analysis['detailed_analysis'], 'cost_benefit_ratio'),
            'average_affordability' => $this->calculateAverageRatio($analysis['detailed_analysis'], 'annual_cost_percentage'),
            'analysis_timestamp' => date('c')
        ];
    }

    /**
     * Calculate average ratio across plans
     *
     * @param array $analyses Analysis data
     * @param string $ratioKey Key to average
     * @return float Average value
     */
    private function calculateAverageRatio(array $analyses, string $ratioKey): float
    {
        if (empty($analyses)) {
            return 0.0;
        }

        $total = 0.0;
        $count = 0;

        foreach ($analyses as $analysis) {
            if (isset($analysis[$ratioKey])) {
                $total += $analysis[$ratioKey];
                $count++;
            }
        }

        return $count > 0 ? $total / $count : 0.0;
    }
}