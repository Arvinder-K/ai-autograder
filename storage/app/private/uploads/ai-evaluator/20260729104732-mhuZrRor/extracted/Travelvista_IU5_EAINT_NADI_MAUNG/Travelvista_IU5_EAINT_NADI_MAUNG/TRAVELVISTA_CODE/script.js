function showError(inputId, errorId, message) {
    document.getElementById(inputId).classList.add("error");
    document.getElementById(errorId).textContent = message;
}

function clearError(inputId, errorId) {
    document.getElementById(inputId).classList.remove("error");
    document.getElementById(errorId).textContent = "";
}

function validateForm() {

    let fullname = document.getElementById("fullname").value.trim();
    let email = document.getElementById("email").value.trim();
    let destination = document.getElementById("destination").value.trim();
    let message = document.getElementById("message").value.trim();

    let isValid = true;

    clearError("fullname", "fullnameError");
    clearError("email", "emailError");
    clearError("destination", "destinationError");
    clearError("message", "messageError");

    // Blank validation

    if (fullname === "") {
        showError("fullname", "fullnameError", "Name cannot be blank.");
        isValid = false;
    }

    if (email === "") {
        showError("email", "emailError", "Email cannot be blank.");
        isValid = false;
    }

    if (destination === "") {
        showError("destination", "destinationError", "Destination cannot be blank.");
        isValid = false;
    }

    if (message === "") {
        showError("message", "messageError", "Message cannot be blank.");
        isValid = false;
    }

    // Email validation

    if (email !== "") {
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let atCount = (email.match(/@/g) || []).length;

        if (!email.includes("@")) {
            showError("email", "emailError", "Email must contain @ symbol.");
            isValid = false;
        }
        else if (atCount > 1) {
            showError("email", "emailError", "Email cannot contain multiple @ symbols.");
            isValid = false;
        }
        else if (!emailRegex.test(email)) {
            showError("email", "emailError", "Invalid email format.");
            isValid = false;
        }
    }

    // Success

    if (isValid) {

        alert("Form submitted successfully!");

        document.getElementById("fullname").value = "";
        document.getElementById("email").value = "";
        document.getElementById("destination").value = "";
        document.getElementById("message").value = "";

    }
}