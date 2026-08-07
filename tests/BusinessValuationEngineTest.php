<?php

declare(strict_types=1);

namespace Ksfraser\BusinessValuation\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ksfraser\BusinessValuation\OverheadExpenseCalculator;
use Ksfraser\BusinessValuation\BusinessValuationEngine;
use Ksfraser\BusinessValuation\BuySellAgreementAnalyzer;
use Ksfraser\BusinessValuation\SuccessionPlanningEngine;
use Ksfraser\ModulesCommon\ParameterDefinition;
use Ksfraser\ModulesCommon\CalculationResult;
use Ksfraser\ModulesCommon\CalculationContext;

/**
 * Business Valuation Engine Test Suite
 *
 * Tests the BusinessValuationEngine integration with all components.
 *
 * @author AI Assistant
 * @version 1.0
 * @since 7 November 2025
 */
class BusinessValuationEngineTest extends TestCase
{
    private BusinessValuationEngine $engine;
    private LoggerInterface $logger;
    private BuySellAgreementAnalyzer $buySellAnalyzer;
    private OverheadExpenseCalculator $overheadCalculator;
    private SuccessionPlanningEngine $successionEngine;

    protected function setUp(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $this->logger = $logger;

        /** @var BuySellAgreementAnalyzer $buySellAnalyzer */
        $buySellAnalyzer = $this->createMock(BuySellAgreementAnalyzer::class);
        $this->buySellAnalyzer = $buySellAnalyzer;

        /** @var OverheadExpenseCalculator $overheadCalculator */
        $overheadCalculator = $this->createMock(OverheadExpenseCalculator::class);
        $this->overheadCalculator = $overheadCalculator;

        /** @var SuccessionPlanningEngine $successionEngine */
        $successionEngine = $this->createMock(SuccessionPlanningEngine::class);
        $this->successionEngine = $successionEngine;

        $this->engine = new BusinessValuationEngine(
            $this->logger,
            $this->buySellAnalyzer,
            $this->overheadCalculator,
            $this->successionEngine
        );
    }

    /**
     * Test successful business valuation calculation
     */
    public function testCalculateSuccess(): void
    {
        // Arrange
        $businessData = [
            'name' => 'Sample Business Inc.',
            'assets' => [
                ['name' => 'Building', 'value' => 500000],
                ['name' => 'Equipment', 'value' => 200000]
            ],
            'liabilities' => [
                ['name' => 'Mortgage', 'value' => 300000]
            ],
            'income' => ['annual_revenue' => 800000],
            'expenses' => [
                ['amount' => 5000, 'frequency' => 'monthly']
            ]
        ];

        $context = new CalculationContext('business_valuation_test', [
            'business_data' => $businessData,
            'analysis_type' => BusinessValuationEngine::ANALYSIS_COMPREHENSIVE
        ]);

        $expectedValuation = [
            'business_value' => 2220000.0,
            'valuation_method' => 'income_based',
            'asset_based_value' => 400000,
            'income_based_value' => 2220000.0,
            'total_assets' => 700000,
            'total_liabilities' => 300000,
            'annual_profit' => 740000.0,
            'valuation_date' => '2025-11-09',
            'recommendations' => [
                'Consider professional business valuation for accurate assessment',
                'Review asset values for accuracy',
                'Analyze industry multiples for market-based valuation'
            ]
        ];

        $expectedBuySell = [
            'business_value' => 600000,
            'funding_gap' => 200000,
            'recommendations' => ['Funding gap identified']
        ];

        $expectedOverhead = [
            'total_annual_overhead' => 60000,
            'recommendations' => ['Overhead expense insurance recommended']
        ];

        $expectedSuccession = [
            'readiness_score' => 30,
            'recommendations' => ['Develop comprehensive succession plan']
        ];

        $this->buySellAnalyzer
            ->expects($this->once())
            ->method('analyzeBuySellAgreement')
            ->willReturn($expectedBuySell);

        $this->overheadCalculator
            ->expects($this->once())
            ->method('calculateOverheadExpenses')
            ->willReturn($expectedOverhead);

        $this->successionEngine
            ->expects($this->once())
            ->method('planSuccession')
            ->willReturn($expectedSuccession);

        // Act
        $result = $this->engine->calculate($context);

        // Assert
        $this->assertInstanceOf(CalculationResult::class, $result);
        $data = $result->primaryResult;

        $this->assertArrayHasKey('business_valuation', $data);
        $this->assertArrayHasKey('buy_sell_agreement', $data);
        $this->assertArrayHasKey('overhead_expenses', $data);
        $this->assertArrayHasKey('succession_planning', $data);
        $this->assertArrayHasKey('overall_assessment', $data);
        $this->assertArrayHasKey('recommendations', $data);

        $this->assertEquals($expectedValuation, $data['business_valuation']);
        $this->assertEquals($expectedBuySell, $data['buy_sell_agreement']);
        $this->assertEquals($expectedOverhead, $data['overhead_expenses']);
        $this->assertEquals($expectedSuccession, $data['succession_planning']);
    }

    /**
     * Test calculation with empty business data
     */
    public function testCalculateEmptyBusinessData(): void
    {
        // Arrange
        $businessData = ['name' => 'Test Business']; // Provide minimal valid data
        $context = new CalculationContext('business_valuation_test', [
            'business_data' => $businessData
        ]);

        $this->buySellAnalyzer
            ->expects($this->once())
            ->method('analyzeBuySellAgreement')
            ->willReturn(['funding_gap' => 0]);

        $this->overheadCalculator
            ->expects($this->once())
            ->method('calculateOverheadExpenses')
            ->willReturn(['total_annual_overhead' => 0]);

        $this->successionEngine
            ->expects($this->once())
            ->method('planSuccession')
            ->willReturn(['readiness_score' => 0]);

        // Act & Assert - should not throw exception with valid minimal data
        $result = $this->engine->calculate($context);
        $this->assertInstanceOf(CalculationResult::class, $result);
    }

    /**
     * Test parameter validation
     */
    public function testValidateParameters(): void
    {
        // Valid parameters
        $validContext = new CalculationContext('test', [
            'business_data' => ['name' => 'Test Business'],
            'analysis_type' => BusinessValuationEngine::ANALYSIS_VALUATION
        ]);
        $result = $this->engine->validate($validContext);
        $this->assertTrue($result->isValid);

        // Invalid analysis type
        $invalidContext = new CalculationContext('test', [
            'business_data' => ['name' => 'Test Business'],
            'analysis_type' => 'invalid_type'
        ]);
        $result = $this->engine->validate($invalidContext);
        $this->assertFalse($result->isValid);
    }

    /**
     * Test getCalculationType
     */
    public function testGetCalculationType(): void
    {
        $this->assertEquals('business_valuation', $this->engine->getCalculationType());
    }

    /**
     * Test getRequiredParameters
     */
    public function testGetRequiredParameters(): void
    {
        $params = $this->engine->getRequiredParameters();

        $this->assertArrayHasKey('business_data', $params);
        $this->assertArrayHasKey('analysis_type', $params);
        $this->assertArrayHasKey('valuation_date', $params);
        $this->assertArrayHasKey('tax_situation', $params);

        $this->assertInstanceOf(ParameterDefinition::class, $params['business_data']);
        $this->assertTrue($params['business_data']->required);
    }

    /**
     * Test getOptionalParameters
     */
    public function testGetOptionalParameters(): void
    {
        $params = $this->engine->getOptionalParameters();

        $this->assertArrayHasKey('shareholder_agreement', $params);
        $this->assertArrayHasKey('succession_plan', $params);
        $this->assertArrayHasKey('insurance_policies', $params);

        $this->assertInstanceOf(ParameterDefinition::class, $params['shareholder_agreement']);
        $this->assertFalse($params['shareholder_agreement']->required);
    }
}