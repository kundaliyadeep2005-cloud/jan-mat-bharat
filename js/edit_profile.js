// Wait for the HTML page to load completely before running this code
$(document).ready(function () {
    console.log("Bootstrap Edit Profile Validation Loaded");

    // Listen for the 'submit' action on the edit profile form
    $("#editProfileForm").on("submit", function (event) {

        // 1. Prevent form from submitting immediately.
        event.preventDefault();

        // Assume the form is valid to start with
        let isValid = true;

        // --- HELPER FUNCTIONS FOR BOOTSTRAP ---
        function showError(selector, message) {
            $(selector).addClass("is-invalid");
            // Find the invalid-feedback div that is a sibling of the input
            $(selector).siblings(".invalid-feedback").text(message);
            isValid = false;
        }

        function showSuccess(selector) {
            $(selector).removeClass("is-invalid");
            $(selector).addClass("is-valid");
        }

        // --- RESET STATE ---
        $(".form-control").removeClass("is-invalid is-valid");
        $(".invalid-feedback").text("");

        // Get values
        let name = $("#name").val() || "";
        let email = $("#email").val() || "";
        let dob = $("#dob").val() || "";
        let state = $("#state").val() || "";
        let currentPass = $("#current_password").val() || "";

        name = name.trim();
        email = email.trim();

        // --- VALIDATE NAME ---
        if (name === "") {
            showError("#name", "Please enter your full name.");
        } else if (name.length < 2) {
            showError("#name", "Name is too short (min 2 letters).");
        } else {
            showSuccess("#name");
        }

        // --- VALIDATE EMAIL ---
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            showError("#email", "Please enter your email address.");
        } else if (!emailPattern.test(email)) {
            showError("#email", "This doesn't look like a valid email.");
        } else {
            showSuccess("#email");
        }

        // --- VALIDATE DATE OF BIRTH (18+) ---
        if (dob === "") {
            showError("#dob", "Please enter your date of birth.");
        } else {
            let birthDate = new Date(dob);
            let today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            let monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < 18) {
                showError("#dob", "You must be at least 18 years old.");
            } else {
                showSuccess("#dob");
            }
        }

        // --- VALIDATE STATE ---
        if (!state || state === "") {
            showError("#state", "Please select your state.");
        } else {
            showSuccess("#state");
        }

        // --- VALIDATE CURRENT PASSWORD (MANDATORY) ---
        if (currentPass === "") {
            showError("#current_password", "Required for authorization.");
        } else {
            showSuccess("#current_password");
        }

        // If everything is valid, submit the form programmatically
        if (isValid === true) {
            this.submit();
        }

    });

});

/**
 * Preview the selected profile image before uploading.
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#profilePreview').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
