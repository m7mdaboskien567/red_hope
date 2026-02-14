document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (!loginForm) return;

  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  if (togglePassword) {
    togglePassword.addEventListener("click", () => {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      const icon = togglePassword.querySelector("i");
      icon.classList.toggle("bi-eye");
      icon.classList.toggle("bi-eye-slash");
    });
  }

  const rememberedEmail = localStorage.getItem("rememberedEmail");
  if (rememberedEmail && emailInput) {
    emailInput.value = rememberedEmail;
    const rememberMeCheck = document.getElementById("rememberMe");
    if (rememberMeCheck) rememberMeCheck.checked = true;
  }

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    console.log("🚀 Login attempt started...");

    emailInput.classList.remove("error", "success");
    passwordInput.classList.remove("error", "success");

    let isValid = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailInput.value.trim())) {
      emailInput.classList.add("error");
      isValid = false;
    } else {
      emailInput.classList.add("success");
    }

    if (passwordInput.value.length < 4) {
      passwordInput.classList.add("error");
      isValid = false;
    } else {
      passwordInput.classList.add("success");
    }

    if (isValid) {
      const submitBtn = loginForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      const rememberMe = document.getElementById("rememberMe")?.checked;

      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Signing in...';
      submitBtn.disabled = true;

      const formData = new FormData();
      formData.append("email", emailInput.value);
      formData.append("password", passwordInput.value);

      try {
        const response = await fetch("apis/login.php", {
          method: "POST",
          body: formData,
        });

        const data = await response.json();
        console.log("📥 Server Response:", data);

        if (data.success) {
          if (rememberMe) {
            localStorage.setItem("rememberedEmail", emailInput.value);
          } else {
            localStorage.removeItem("rememberedEmail");
          }

          if (data.token) {
            console.log("💾 Storing JWT Token in localStorage...");
            localStorage.setItem("redhope_jwt", data.token);
            console.log(
              "✅ Token successfully stored:",
              localStorage.getItem("redhope_jwt"),
            );
          } else {
            console.warn("⚠️ No token received from server!");
          }

          submitBtn.classList.remove("btn-login");
          submitBtn.classList.add("btn-success");
          submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Success!';

          setTimeout(() => {
            window.location.href = "dashboard.php";
          }, 800);
        } else {
          showAlert(data.message || "Login failed.", "error");
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
          passwordInput.classList.add("error");
        }
      } catch (error) {
        console.error("❌ Login Error:", error);
        showAlert("A system error occurred.", "error");
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    }
  });

  const socialButtons = document.querySelectorAll(".social-btn");
  socialButtons.forEach((btn) => {
    btn.addEventListener("click", () =>
      showAlert("Social login is disabled.", "info"),
    );
  });

  const forgotPasswordLink = document.querySelector(".forgot-password");
  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener("click", (e) => {
      e.preventDefault();
      showAlert("Password recovery unavailable.", "warning");
    });
  }
});
