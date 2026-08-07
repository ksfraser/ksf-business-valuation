<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation;

/**
 * Succession Planning Engine
 *
 * Analyzes business succession planning needs and tax implications.
 * Provides recommendations for smooth business transitions and tax optimization.
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class SuccessionPlanningEngine
{
    /**
     * Plan business succession
     */
    public function planSuccession(array $businessData, array $valuationAnalysis, array $taxSituation): array
    {
        $businessValue = $valuationAnalysis['business_value'];
        $shareholders = $businessData['shareholders'] ?? [];
        $successionPlan = $businessData['succession_plan'] ?? [];

        // Assess current succession readiness
        $readinessScore = $this->assessSuccessionReadiness($successionPlan);

        // Calculate tax implications
        $taxImplications = $this->calculateTaxImplications($businessValue, $shareholders, $taxSituation);

        // Generate succession strategies
        $strategies = $this->generateSuccessionStrategies($businessValue, $shareholders, $taxImplications);

        return [
            'readiness_score' => $readinessScore,
            'readiness_level' => $this->getReadinessLevel($readinessScore),
            'tax_implications' => $taxImplications,
            'succession_strategies' => $strategies,
            'timeline' => $this->createSuccessionTimeline($successionPlan),
            'recommendations' => $this->generateSuccessionRecommendations($readinessScore, $taxImplications)
        ];
    }

    /**
     * Assess succession readiness
     */
    private function assessSuccessionReadiness(array $successionPlan): int
    {
        $score = 0;
        $maxScore = 100;

        // Check for documented succession plan
        if (!empty($successionPlan)) {
            $score += 20;
        }

        // Check for identified successor
        if (isset($successionPlan['successor'])) {
            $score += 20;
        }

        // Check for funding mechanism
        if (isset($successionPlan['funding_mechanism'])) {
            $score += 15;
        }

        // Check for transition timeline
        if (isset($successionPlan['transition_timeline'])) {
            $score += 15;
        }

        // Check for contingency plans
        if (isset($successionPlan['contingency_plans'])) {
            $score += 10;
        }

        // Check for family involvement
        if (isset($successionPlan['family_involvement'])) {
            $score += 10;
        }

        // Check for professional advisors
        if (isset($successionPlan['professional_advisors'])) {
            $score += 10;
        }

        return min($score, $maxScore);
    }

    /**
     * Get readiness level description
     */
    private function getReadinessLevel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            $score >= 20 => 'Poor',
            default => 'Critical'
        };
    }

    /**
     * Calculate tax implications of succession
     */
    private function calculateTaxImplications(float $businessValue, array $shareholders, array $taxSituation): array
    {
        $marginalRate = $taxSituation['marginal_rate'] ?? 0.45;
        $province = $taxSituation['province'] ?? 'Ontario';

        // Estimate capital gains tax
        $estimatedCapitalGainsTax = $businessValue * $marginalRate * 0.5; // Assume 50% tax rate on gains

        // Estimate probate fees (Ontario example)
        $probateFees = $this->calculateProbateFees($businessValue, $province);

        // Estimate executor fees
        $executorFees = $businessValue * 0.02; // 2% of estate value

        $totalTaxesAndFees = $estimatedCapitalGainsTax + $probateFees + $executorFees;

        return [
            'estimated_capital_gains_tax' => $estimatedCapitalGainsTax,
            'probate_fees' => $probateFees,
            'executor_fees' => $executorFees,
            'total_taxes_and_fees' => $totalTaxesAndFees,
            'after_tax_value' => $businessValue - $totalTaxesAndFees,
            'effective_tax_rate' => $businessValue > 0 ? ($totalTaxesAndFees / $businessValue) : 0
        ];
    }

    /**
     * Calculate probate fees (simplified, Ontario example)
     */
    private function calculateProbateFees(float $value, string $province): float
    {
        // Simplified probate calculation for Ontario
        if ($province === 'Ontario') {
            if ($value <= 50000) {
                return 0;
            } elseif ($value <= 100000) {
                return 0.005 * ($value - 50000);
            } elseif ($value <= 200000) {
                return 275 + 0.01 * ($value - 100000);
            } else {
                return 1275 + 0.015 * ($value - 200000);
            }
        }

        // Default: assume 0.5% for other provinces
        return $value * 0.005;
    }

    /**
     * Generate succession strategies
     */
    private function generateSuccessionStrategies(float $businessValue, array $shareholders, array $taxImplications): array
    {
        $strategies = [];

        // Installment sale strategy
        $strategies[] = [
            'name' => 'Installment Sale',
            'description' => 'Sell business over multiple years to spread tax liability',
            'tax_advantage' => 'Defers capital gains tax',
            'estimated_savings' => $taxImplications['estimated_capital_gains_tax'] * 0.3,
            'complexity' => 'Medium'
        ];

        // Life insurance strategy
        $strategies[] = [
            'name' => 'Life Insurance Funding',
            'description' => 'Use life insurance to fund buy-sell agreement',
            'tax_advantage' => 'Tax-free death benefit',
            'estimated_savings' => $taxImplications['total_taxes_and_fees'] * 0.8,
            'complexity' => 'Low'
        ];

        // Estate freeze strategy
        $strategies[] = [
            'name' => 'Estate Freeze',
            'description' => 'Freeze estate value to minimize future growth taxation',
            'tax_advantage' => 'Shifts future growth to next generation',
            'estimated_savings' => $businessValue * 0.2 * $taxImplications['effective_tax_rate'],
            'complexity' => 'High'
        ];

        return $strategies;
    }

    /**
     * Create succession timeline
     */
    private function createSuccessionTimeline(array $successionPlan): array
    {
        $existingTimeline = $successionPlan['transition_timeline'] ?? [];

        // Create default timeline if none exists
        if (empty($existingTimeline)) {
            return [
                'immediate' => ['Identify successor', 'Document current operations'],
                '6_months' => ['Develop transition plan', 'Begin successor training'],
                '1_year' => ['Implement funding mechanisms', 'Transfer key responsibilities'],
                '2_years' => ['Full transition of operations', 'Legal documentation'],
                'retirement' => ['Final transfer', 'Ongoing monitoring']
            ];
        }

        return $existingTimeline;
    }

    /**
     * Generate succession recommendations
     */
    private function generateSuccessionRecommendations(int $readinessScore, array $taxImplications): array
    {
        $recommendations = [];

        if ($readinessScore < 40) {
            $recommendations[] = 'URGENT: Develop comprehensive succession plan immediately';
            $recommendations[] = 'Identify and train potential successors';
        } elseif ($readinessScore < 70) {
            $recommendations[] = 'Enhance succession planning with funding mechanisms';
            $recommendations[] = 'Document critical business processes';
        } else {
            $recommendations[] = 'Succession planning is well developed - regular reviews recommended';
        }

        if ($taxImplications['effective_tax_rate'] > 0.3) {
            $recommendations[] = 'High tax burden - consider tax-advantaged succession strategies';
            $recommendations[] = 'Consult tax professional for estate planning optimization';
        }

        $recommendations[] = 'Review succession plan annually';
        $recommendations[] = 'Include family members in planning discussions';
        $recommendations[] = 'Consider professional advisory team (lawyer, accountant, insurance specialist)';

        return $recommendations;
    }
}