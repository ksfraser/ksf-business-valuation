# FR-004-004 OverheadExpense: Overhead Expense

**Related:** BR-004 Business Valuation, UC-004-001 BusinessValuationUseCases
**Engine:** `Ksfraser\\BusinessValuation\\OverheadExpenseCalculator`

## Description
Compute business overhead for valuation.

## Primary actor
Advisor (or System on recalculation).

## Preconditions
Client data available in FA.

## Main flow
1. Advisor invokes the calculation for the client.
2. System applies the engine and returns the result.

## Postconditions
Calculation result available for the client's plan summary.
