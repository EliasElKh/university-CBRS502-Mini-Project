const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");
const loginBtn = document.querySelector(".login-btn");

togglePassword.addEventListener("click", () => {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    togglePassword.classList.toggle("fa-eye");
    togglePassword.classList.toggle("fa-eye-slash");
});

// Google OAuth callback
function handleCredentialResponse(response) {
    const jwt = response.credential;
    localStorage.setItem("google_jwt", jwt);
    window.location.href = "success.php";
}

// Render Google Button
window.onload = () => {
    google.accounts.id.renderButton(
        document.getElementById("googleSignIn"),
        { theme: "outline", size: "large", width: "100%" }
    );
};

loginBtn.addEventListener("click", () => {
    window.location.href = "success.php";
});
