"""
Task 2: Unit Testing for Budget Buddy
-------------------------------------

This script tests the validate_expense() function
using only the 3 test cases from Task 1.

Test Cases:
1. Normal Expense
2. Negative Entry
3. Over Budget
"""

# Import unittest library
import unittest

# Import the function from budget_logic.py
from budget_logic import validate_expense


# Create Test Class
class TestBudgetLogic(unittest.TestCase):

    # Test Case 1: Normal Expense
    def test_normal_expense(self):

        # amount is positive and within budget
        result = validate_expense(5000, 20000)

        # Expected result = True
        self.assertTrue(result)

    # Test Case 2: Negative Entry
    def test_negative_entry(self):

        # amount is negative
        result = validate_expense(-1000, 20000)

        # Expected result = False
        self.assertFalse(result)

    # Test Case 3: Over Budget
    def test_over_budget(self):

        # amount is greater than budget
        result = validate_expense(25000, 20000)

        # Expected result = False
        self.assertFalse(result)


# Run the unit tests
if __name__ == "__main__":
    unittest.main()