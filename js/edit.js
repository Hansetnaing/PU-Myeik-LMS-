function toggleEditForm() {
    const editForm = document.getElementById('editForm');
    if (!editForm) return;

    if (editForm.style.display === 'none' || editForm.style.display === '') {
        editForm.style.display = 'block';
    } else {
        editForm.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("profileForm");
    const newPassword = document.getElementById("new_password");
    const confirmPassword = document.getElementById("confirm_password");

    if (!form || !newPassword || !confirmPassword) return;

    form.addEventListener("submit", function (e) {
        const password = newPassword.value;

        // Password rules regex
        const passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{8,}$/;

        if (!passwordRegex.test(password)) {
            e.preventDefault();
            alert(
                "Password must be at least 8 characters and include:\n" +
                "- Uppercase letter\n" +
                "- Lowercase letter\n" +
                "- Number\n" +
                "- Special character"
            );
            return;
        }

        if (password !== confirmPassword.value) {
            e.preventDefault();
            alert("Passwords do not match.");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("new_password");
    const rulesBox = document.getElementById("passwordRules");

    if (!passwordInput || !rulesBox) return;

    passwordInput.addEventListener("input", function () {
        if (passwordInput.value.length > 0) {
            rulesBox.style.display = "block";
        } else {
            rulesBox.style.display = "none";
        }

        const value = passwordInput.value;

        checkRule("rule-length", value.length >= 8);
        checkRule("rule-upper", /[A-Z]/.test(value));
        checkRule("rule-lower", /[a-z]/.test(value));
        checkRule("rule-number", /\d/.test(value));
        checkRule("rule-special", /[@$!%*?&#]/.test(value));
    });

    function checkRule(id, condition) {
        const rule = document.getElementById(id);
        if (condition) {
            rule.style.color = "green";
            rule.innerHTML = "✅ " + rule.textContent.replace(/^[❌✅]\s*/, "");
        } else {
            rule.style.color = "red";
            rule.innerHTML = "❌ " + rule.textContent.replace(/^[❌✅]\s*/, "");
        }
    }
});
