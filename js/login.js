// Wait for the HTML page to load completely
$(document).ready(function () {

    // Listen for the 'submit' action on the login form
    $("#loginForm").on("submit", function (event) {

        // Assume valid initially
        let isValid = true;

        // --- HELPER FUNCTIONS FOR BOOTSTRAP ---
        function showError(selector, message) {
            $(selector).addClass("is-invalid"); // Add red border
            $(selector).siblings(".invalid-feedback").text(message); // Show error text
            isValid = false;
        }

        function showSuccess(selector) {
            $(selector).removeClass("is-invalid"); // Remove red border
            $(selector).addClass("is-valid");      // Add green border (optional)
        }

        // --- RESET VALIDATION STATE ---
        // Remove error classes before re-checking
        $(".form-control").removeClass("is-invalid is-valid");
        $(".invalid-feedback").text("");

        // --- GET VALUES ---
        let email = $("#email").val().trim();
        let password = $("#password").val();

        // --- VALIDATE EMAIL ---
        if (email === "") {
            showError("#email", "Please enter your email address.");
        } else {
            showSuccess("#email");
        }

        // --- VALIDATE PASSWORD ---
        if (password === "") {
            showError("#password", "Please enter your password.");
        } else {
            showSuccess("#password");
        }

        // --- FINAL CHECK ---
        if (isValid === false) {
            event.preventDefault(); // Stop form submission if there are errors
        }

    });

});
