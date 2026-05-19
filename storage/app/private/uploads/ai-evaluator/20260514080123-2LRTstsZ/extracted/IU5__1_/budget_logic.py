# Function to check if a student's expense is allowed
def validate_expense(amount, budget):
    
    # Rule 1: Make sure the expense is more than zero
    # This stops negative numbers from adding money to the budget
    if amount <= 0:
        return False

    # Rule 2: Make sure the student has enough money left
    # This stops the balance from going below zero
    if amount > budget:
        return False

    # If the amount passes both rules, return True
    return True