import unittest
from budget_logic import validate_expense


class TestBudget(unittest.TestCase):

    # Test Case 1: A normal purchase that is within the budget
    def test_normal_expense(self):
        # Spending 50 with 100 in the budget should be allowed
        self.assertTrue(validate_expense(50, 100))

    # Test Case 2: Entering a negative number or zero
    def test_negative_entry(self):
        # A negative expense like -10 should be blocked
        self.assertFalse(validate_expense(-10, 100))

    # Test Case 3: Spending more than the monthly allowance
    def test_over_budget(self):
        # Spending 150 when you only have 100 should be blocked
        self.assertFalse(validate_expense(150, 100))


if __name__ == "__main__":
    unittest.main()
