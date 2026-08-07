# FR-004-005 Cost-BenefitAnalysis: Cost-Benefit Analysis

**Related:** BR-004 Business Valuation, UC-004-001 BusinessValuationUseCases
**Engine:** `Ksfraser\\BusinessValuation\\CostBenefitCalculator`

## Description
Compare costs vs benefits of planning options.

## Primary actor
Advisor (or System on recalculation).

## Preconditions
Client data available in FA.

## Main flow
1. Advisor invokes the calculation for the client.
2. System applies the engine and returns the result.

## Postconditions
Calculation result available for the client's plan summary.
