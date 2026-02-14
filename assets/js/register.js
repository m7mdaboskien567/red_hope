let currentStep = 1;
const totalSteps = 5;

const formSteps = document.querySelectorAll(".form-step");
const progressSteps = document.querySelectorAll(".step");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const submitBtn = document.getElementById("submitBtn");
const registerForm = document.getElementById("registerForm");

const formData = {
  userType: "",
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  gender: "",
  dob: "",
  bloodType: "",
  password: "",
  confirmPassword: "",
  terms: false,
};

document.addEventListener("DOMContentLoaded", () => {
  showStep(1);
  updateButtons();
});

function showStep(step) {
  formSteps.forEach((formStep, index) => {
    if (index + 1 === step) {
      formStep.classList.add("active");
    } else {
      formStep.classList.remove("active");
    }
  });

  progressSteps.forEach((progressStep, index) => {
    if (index + 1 === step) {
      progressStep.classList.add("active");
      progressStep.classList.remove("completed");
    } else if (index + 1 < step) {
      progressStep.classList.add("completed");
      progressStep.classList.remove("active");
    } else {
      progressStep.classList.remove("active", "completed");
    }
  });

  currentStep = step;
  updateButtons();
  updateBloodTypeVisibility();
}

function updateButtons() {
  if (currentStep === 1) {
    prevBtn.style.display = "none";
  } else {
    prevBtn.style.display = "flex";
  }

  if (currentStep === totalSteps) {
    nextBtn.style.display = "none";
    submitBtn.style.display = "flex";
  } else {
    nextBtn.style.display = "flex";
    submitBtn.style.display = "none";
  }
}

function validateStep(step) {
  const currentFormStep = formSteps[step - 1];
  const inputs = currentFormStep.querySelectorAll(
    "input[required], select[required]",
  );
  let isValid = true;

  inputs.forEach((input) => {
    input.classList.remove("error", "success");

    if (input.type === "radio") {
      const radioGroup = currentFormStep.querySelectorAll(
        `input[name="${input.name}"]`,
      );
      const isChecked = Array.from(radioGroup).some((radio) => radio.checked);

      if (!isChecked && input === radioGroup[0]) {
        isValid = false;
        input.parentElement.style.border = "2px solid #dc3545";
        setTimeout(() => {
          input.parentElement.style.border = "";
        }, 1000);
      }
    } else if (input.type === "checkbox") {
      if (!input.checked) {
        isValid = false;
        input.classList.add("error");
      } else {
        input.classList.add("success");
      }
    } else {
      if (!input.value.trim()) {
        isValid = false;
        input.classList.add("error");
      } else {
        if (input.type === "email") {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(input.value)) {
            isValid = false;
            input.classList.add("error");
          } else {
            input.classList.add("success");
          }
        } else if (input.id === "phone") {
          const phoneRegex = /^[\d\s\+\-\(\)]+$/;
          if (!phoneRegex.test(input.value) || input.value.length < 10) {
            isValid = false;
            input.classList.add("error");
          } else {
            input.classList.add("success");
          }
        } else if (input.id === "password") {
          if (input.value.length < 8) {
            isValid = false;
            input.classList.add("error");
          } else {
            input.classList.add("success");
          }
        } else if (input.id === "confirmPassword") {
          const password = document.getElementById("password").value;
          if (input.value !== password) {
            isValid = false;
            input.classList.add("error");
            showAlert("Passwords do not match!", "error");
          } else {
            input.classList.add("success");
          }
        } else {
          input.classList.add("success");
        }
      }
    }
  });

  return isValid;
}

nextBtn.addEventListener("click", () => {
  if (validateStep(currentStep)) {
    saveStepData(currentStep);
    if (currentStep < totalSteps) {
      showStep(currentStep + 1);
    }
  } else {
    showAlert("Please fill in all required fields correctly.", "error");
  }
});

prevBtn.addEventListener("click", () => {
  if (currentStep > 1) {
    showStep(currentStep - 1);
  }
});

function saveStepData(step) {
  switch (step) {
    case 1:
      const userType = document.querySelector('input[name="userType"]:checked');
      if (userType) formData.userType = userType.value;
      break;
    case 2:
      formData.firstName = document.getElementById("firstName").value;
      formData.lastName = document.getElementById("lastName").value;
      break;
    case 3:
      formData.email = document.getElementById("email").value;
      formData.phone = document.getElementById("phone").value;
      break;
    case 4:
      const gender = document.querySelector('input[name="gender"]:checked');
      if (gender) formData.gender = gender.value;
      formData.dob = document.getElementById("dob").value;
      const bloodType = document.querySelector(
        'input[name="bloodType"]:checked',
      );
      if (bloodType) formData.bloodType = bloodType.value;
      break;
    case 5:
      formData.password = document.getElementById("password").value;
      formData.confirmPassword =
        document.getElementById("confirmPassword").value;
      formData.terms = document.getElementById("terms").checked;
      break;
  }
}

registerForm.addEventListener("submit", (e) => {
  e.preventDefault();

  if (validateStep(currentStep)) {
    saveStepData(currentStep);

    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm"></span> Creating Account...';
    submitBtn.disabled = true;

    const fetchFormData = new FormData();
    for (const key in formData) {
      fetchFormData.append(key, formData[key]);
    }

    fetch("apis/add_user.php", {
      method: "POST",
      body: fetchFormData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert("Registration successful! Welcome to RedHope.", "success");
          window.location.href = "login.php";
        } else {
          showAlert(
            data.message || "Registration failed. Please try again.",
            "error",
          );
          submitBtn.innerHTML = 'Create Account <i class="bi bi-check-lg"></i>';
          submitBtn.disabled = false;
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showAlert("A system error occurred. Please try again later.", "error");
        submitBtn.innerHTML = 'Create Account <i class="bi bi-check-lg"></i>';
        submitBtn.disabled = false;
      });
  } else {
    showAlert("Please fill in all required fields correctly.", "error");
  }
});

const userTypeInputs = document.querySelectorAll('input[name="userType"]');

userTypeInputs.forEach((input) => {
  input.addEventListener("change", () => {
    updateBloodTypeVisibility();
  });
});

function updateBloodTypeVisibility() {
  const bloodTypeGroup = document.querySelector(".blood-type-group");
  const userType = document.querySelector('input[name="userType"]:checked');

  if (userType && userType.value === "donor") {
    bloodTypeGroup.classList.add("show");
    bloodTypeGroup.querySelectorAll("input").forEach((input) => {
      input.required = true;
    });
  } else {
    bloodTypeGroup.classList.remove("show");
    bloodTypeGroup.querySelectorAll("input").forEach((input) => {
      input.required = false;
      input.checked = false;
    });
  }
}

const togglePasswordBtns = document.querySelectorAll(".toggle-password");

togglePasswordBtns.forEach((btn) => {
  btn.addEventListener("click", () => {
    const targetId = btn.getAttribute("data-target");
    const input = document.getElementById(targetId);

    const type =
      input.getAttribute("type") === "password" ? "text" : "password";
    input.setAttribute("type", type);

    const icon = btn.querySelector("i");
    icon.classList.toggle("bi-eye");
    icon.classList.toggle("bi-eye-slash");
  });
});

const passwordInput = document.getElementById("password");
const strengthBar = document.querySelector(".strength-bar");

passwordInput.addEventListener("input", () => {
  const password = passwordInput.value;
  let strength = 0;

  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;

  strengthBar.className = "strength-bar";

  if (strength === 0) {
    strengthBar.style.width = "0%";
  } else if (strength <= 2) {
    strengthBar.classList.add("weak");
  } else if (strength === 3) {
    strengthBar.classList.add("medium");
  } else {
    strengthBar.classList.add("strong");
  }
});

const dobInput = document.getElementById("dob");

const today = new Date();
const maxDate = new Date(
  today.getFullYear() - 18,
  today.getMonth(),
  today.getDate(),
);
dobInput.max = maxDate.toISOString().split("T")[0];

const minDate = new Date(
  today.getFullYear() - 100,
  today.getMonth(),
  today.getDate(),
);
dobInput.min = minDate.toISOString().split("T")[0];

document.addEventListener("keydown", (e) => {
  if (e.key === "Enter" && currentStep < totalSteps) {
    e.preventDefault();
    nextBtn.click();
  }

  if (
    e.key === "ArrowRight" &&
    currentStep < totalSteps &&
    !e.target.matches("input, select, textarea")
  ) {
    nextBtn.click();
  }

  if (
    e.key === "ArrowLeft" &&
    currentStep > 1 &&
    !e.target.matches("input, select, textarea")
  ) {
    prevBtn.click();
  }
});

const allInputs = document.querySelectorAll(".form-control");

allInputs.forEach((input) => {
  input.addEventListener("blur", () => {
    if (input.value.trim()) {
      input.classList.remove("error");

      if (input.type === "email") {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test(input.value)) {
          input.classList.add("success");
        } else {
          input.classList.add("error");
        }
      } else if (input.id === "phone") {
        const phoneRegex = /^[\d\s\+\-\(\)]+$/;
        if (phoneRegex.test(input.value) && input.value.length >= 10) {
          input.classList.add("success");
        } else {
          input.classList.add("error");
        }
      } else {
        input.classList.add("success");
      }
    }
  });

  input.addEventListener("focus", () => {
    input.classList.remove("error", "success");
  });
});

const radioGroups = ["userType", "gender"];

radioGroups.forEach((groupName) => {
  const radios = document.querySelectorAll(`input[name="${groupName}"]`);
  radios.forEach((radio) => {
    radio.addEventListener("change", () => {
      setTimeout(() => {
        if (currentStep < totalSteps) {
          nextBtn.click();
        }
      }, 500);
    });
  });
});
