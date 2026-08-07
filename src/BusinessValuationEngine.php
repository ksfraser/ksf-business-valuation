<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation;

use Psr\Log\LoggerInterface;
use Ksfraser\ModulesCommon\ParameterDefinition;
use Ksfraser\ModulesCommon\ValidationResult;
use Ksfraser\ModulesCommon\CalculationResult;
use Ksfraser\ModulesCommon\CalculationEngineInterface;
use Ksfraser\ModulesCommon\CalculationContext;

/**
 * Business Valuation Engine
 *
 * Comprehensive business valuation and succession planning calculations.
 * Implements buySell.xlsm and businessCI.xlsm functionality for business
 * valuation, buy-sell agreements, overhead expense analysis, and succession planning.
 *
 * Single Responsibility: Provide comprehensive business valuation and succession planning
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class BusinessValuationEngine implements CalculationEngineInterface
{
    public const CALCULATION_TYPE = 'business_valuation';
    public const ANALYSIS_VALUATION = 'valuation';
    public const ANALYSIS_BUY_SELL = 'buy_sell';
    public const ANALYSIS_OVERHEAD = 'overhead';
    public const ANALYSIS_SUCCESSION = 'succession';
    public const ANALYSIS_COMPREHENSIVE = 'comprehensive';

    private LoggerInterface $logger;
    private BuySellAgreementAnalyzer $buySellAnalyzer;
    private OverheadExpenseCalculator $overheadCalculator;
    private SuccessionPlanningEngine $successionEngine;

    /**
     * Constructor
     */
    public function __construct(
        LoggerInterface $logger,
        BuySellAgreementAnalyzer $buySellAnalyzer,
        OverheadExpenseCalculator $overheadCalculator,
        SuccessionPlanningEngine $successionEngine
    ) {
        $this->logger = $logger;
        $this->buySellAnalyzer = $buySellAnalyzer;
        $this->overheadCalculator = $overheadCalculator;
        $this->successionEngine = $successionEngine;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(CalculationContext $context): CalculationResult
    {
        try {
            // Validate input parameters
            $validationResult = $this->validate($context);

            if (!$validationResult->isValid) {
                throw new CalculationException(
                    'Parameter validation failed: ' . implode(', ', $validationResult->errors),
                    self::CALCULATION_TYPE
                );
            }

            // Extract analysis parameters
            $businessData = $context->parameters['business_data'];
            $analysisType = $context->parameters['analysis_type'] ?? self::ANALYSIS_COMPREHENSIVE;
            $valuationDate = $context->parameters['valuation_date'] ?? date('Y-m-d');
            $taxSituation = $context->parameters['tax_situation'] ?? [];

            $this->logger->info('Starting business valuation calculation', [
                'analysis_type' => $analysisType,
                'business_name' => $businessData['name'] ?? 'Unknown',
                'valuation_date' => $valuationDate
            ]);

            // Perform comprehensive business analysis
            $valuationAnalysis = $this->performValuationAnalysis($businessData, $valuationDate);
            $buySellAnalysis = $this->buySellAnalyzer->analyzeBuySellAgreement($businessData, $valuationAnalysis);
            $overheadAnalysis = $this->overheadCalculator->calculateOverheadExpenses($businessData);
            $successionAnalysis = $this->successionEngine->planSuccession($businessData, $valuationAnalysis, $taxSituation);

            // Compile comprehensive results
            $primaryResult = $this->compilePrimaryResult(
                $valuationAnalysis,
                $buySellAnalysis,
                $overheadAnalysis,
                $successionAnalysis,
                $analysisType
            );

            $this->logger->info('Business valuation calculation completed successfully', [
                'analysis_type' => $analysisType,
                'business_value' => $valuationAnalysis['business_value'] ?? 0
            ]);

            return CalculationResult::success(
                self::CALCULATION_TYPE,
                $primaryResult,
                [
                    'valuation_analysis' => $valuationAnalysis,
                    'buy_sell_analysis' => $buySellAnalysis,
                    'overhead_analysis' => $overheadAnalysis,
                    'succession_analysis' => $successionAnalysis
                ],
                [], // assumptions used
                [
                    'analysis_type' => $analysisType,
                    'valuation_date' => $valuationDate,
                    'business_name' => $businessData['name'] ?? 'Unknown',
                    'calculation_timestamp' => date('c'),
                    'engine_version' => '1.0'
                ]
            );

        } catch (\Exception $e) {
            $this->logger->error('Business valuation calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CalculationException(
                'Business valuation calculation failed: ' . $e->getMessage(),
                self::CALCULATION_TYPE,
                ['original_exception' => $e],
                $e
            );
        }
    }

    /**
     * Perform business valuation analysis
     */
    private function performValuationAnalysis(array $businessData, string $valuationDate): array
    {
        // Implement business valuation logic
        // This would include asset-based, income-based, and market-based approaches

        $assets = $businessData['assets'] ?? [];
        $liabilities = $businessData['liabilities'] ?? [];
        $income = $businessData['income'] ?? [];
        $expenses = $businessData['expenses'] ?? [];

        // Calculate asset-based value
        $totalAssets = array_sum(array_column($assets, 'value'));
        $totalLiabilities = array_sum(array_column($liabilities, 'value'));
        $netAssetValue = $totalAssets - $totalLiabilities;

        // Calculate income-based value (simplified)
        $annualRevenue = $income['annual_revenue'] ?? 0;
        $annualExpenses = 0;
        foreach ($expenses as $expense) {
            $annualExpenses += $this->annualizeExpense($expense['amount'], $expense['frequency'] ?? 'annual');
        }
        $annualProfit = $annualRevenue - $annualExpenses;
        $incomeValue = $annualProfit * 3; // Simplified 3x multiple

        // Use the higher of asset-based or income-based value
        $businessValue = max($netAssetValue, $incomeValue);

        return [
            'business_value' => $businessValue,
            'valuation_method' => $businessValue === $netAssetValue ? 'asset_based' : 'income_based',
            'asset_based_value' => $netAssetValue,
            'income_based_value' => $incomeValue,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'annual_profit' => $annualProfit,
            'valuation_date' => $valuationDate,
            'recommendations' => [
                'Consider professional business valuation for accurate assessment',
                'Review asset values for accuracy',
                'Analyze industry multiples for market-based valuation'
            ]
        ];
    }

    /**
     * Compile primary result
     */
    private function compilePrimaryResult(
        array $valuationAnalysis,
        array $buySellAnalysis,
        array $overheadAnalysis,
        array $successionAnalysis,
        string $analysisType
    ): array {
        $result = [
            'business_valuation' => $valuationAnalysis,
            'buy_sell_agreement' => $buySellAnalysis,
            'overhead_expenses' => $overheadAnalysis,
            'succession_planning' => $successionAnalysis,
            'overall_assessment' => [
                'business_value' => $valuationAnalysis['business_value'],
                'funding_gap' => $buySellAnalysis['funding_gap'] ?? 0,
                'annual_overhead' => $overheadAnalysis['total_annual_overhead'] ?? 0,
                'succession_readiness' => $successionAnalysis['readiness_score'] ?? 0,
                'analysis_timestamp' => date('c')
            ],
            'recommendations' => []
        ];

        // Add analysis-specific recommendations
        switch ($analysisType) {
            case self::ANALYSIS_VALUATION:
                $result['recommendations'] = $valuationAnalysis['recommendations'] ?? [];
                break;
            case self::ANALYSIS_BUY_SELL:
                $result['recommendations'] = $buySellAnalysis['recommendations'] ?? [];
                break;
            case self::ANALYSIS_OVERHEAD:
                $result['recommendations'] = $overheadAnalysis['recommendations'] ?? [];
                break;
            case self::ANALYSIS_SUCCESSION:
                $result['recommendations'] = $successionAnalysis['recommendations'] ?? [];
                break;
            case self::ANALYSIS_COMPREHENSIVE:
                $result['recommendations'] = array_merge(
                    $valuationAnalysis['recommendations'] ?? [],
                    $buySellAnalysis['recommendations'] ?? [],
                    $overheadAnalysis['recommendations'] ?? [],
                    $successionAnalysis['recommendations'] ?? []
                );
                break;
        }

        return $result;
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
     * {@inheritDoc}
     */
    public function validate(CalculationContext $context): ValidationResult
    {
        return $this->validateParameters($context->parameters);
    }

    /**
     * Validate calculation parameters
     */
    private function validateParameters(array $parameters): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Validate business data
        if (!isset($parameters['business_data']) || !is_array($parameters['business_data'])) {
            $errors[] = 'Business data is required and must be an array';
        } else {
            $businessData = $parameters['business_data'];

            if (!isset($businessData['name']) || empty($businessData['name'])) {
                $errors[] = 'Business name is required';
            }

            if (!isset($businessData['assets']) || !is_array($businessData['assets'])) {
                $warnings[] = 'Business assets should be provided as an array';
            }

            if (!isset($businessData['liabilities']) || !is_array($businessData['liabilities'])) {
                $warnings[] = 'Business liabilities should be provided as an array';
            }
        }

        // Validate analysis type
        $validAnalysisTypes = [
            self::ANALYSIS_VALUATION,
            self::ANALYSIS_BUY_SELL,
            self::ANALYSIS_OVERHEAD,
            self::ANALYSIS_SUCCESSION,
            self::ANALYSIS_COMPREHENSIVE
        ];

        if (isset($parameters['analysis_type']) && !in_array($parameters['analysis_type'], $validAnalysisTypes)) {
            $errors[] = 'Invalid analysis type. Valid types: ' . implode(', ', $validAnalysisTypes);
        }

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    /**
     * {@inheritDoc}
     */
    public function getCalculationType(): string
    {
        return self::CALCULATION_TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredParameters(): array
    {
        return [
            'business_data' => new ParameterDefinition(
                'business_data',
                'array',
                'Business information including name, assets, liabilities, income, and expenses',
                true
            ),
            'analysis_type' => new ParameterDefinition(
                'analysis_type',
                'string',
                'Type of analysis to perform',
                false,
                self::ANALYSIS_COMPREHENSIVE
            ),
            'valuation_date' => new ParameterDefinition(
                'valuation_date',
                'string',
                'Date for valuation calculation',
                false,
                date('Y-m-d')
            ),
            'tax_situation' => new ParameterDefinition(
                'tax_situation',
                'array',
                'Tax situation for succession planning',
                false,
                []
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionalParameters(): array
    {
        return [
            'shareholder_agreement' => new ParameterDefinition(
                'shareholder_agreement',
                'array',
                'Details of shareholder/buy-sell agreement',
                false
            ),
            'succession_plan' => new ParameterDefinition(
                'succession_plan',
                'array',
                'Existing succession planning details',
                false
            ),
            'insurance_policies' => new ParameterDefinition(
                'insurance_policies',
                'array',
                'Business-owned life insurance policies',
                false
            )
        ];
    }
}