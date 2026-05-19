"""
Budget Buddy - Expense Validation Logic
---------------------------------------
This module contains the validation function used
in the fintech mobile application.
"""


def validate_expense(amount, budget):

    # Rule 1: Expense must be positive
    if amount <= 0:
        return False

    # Rule 2: Expense cannot exceed budget
    if amount > budget:
        return False

    # Valid expense
    return True