document.addEventListener("DOMContentLoaded", () => {
  // 1. Synchronize AOS with the Custom Loader
  // We wait for the 'pageLoaded' event from loader.php to initialize AOS
  // This ensures elements don't animate while the loader is still visible
  document.addEventListener("pageLoaded", () => {
    setTimeout(() => {
      AOS.init({
        duration: 600,
        easing: "ease-in-out",
        once: true,
        offset: 40,
        delay: 50,
      });
    }, 600);
  });

  // 2. Go to Top Button Logic
  const scrollTopBtn = document.getElementById("scrollTopBtn");

  if (scrollTopBtn) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 300) {
        scrollTopBtn.classList.add("visible");
      } else {
        scrollTopBtn.classList.remove("visible");
      }
    });

    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }
});

/**
 * Global Alert/Notification System using Bootstrap Toasts
 * @param {string} message - The message to display
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
window.showAlert = function (message, type = "success") {
  const container = document.getElementById("alertContainer");
  if (!container) {
    console.warn("Alert container not found! Falling back to standard alert.");
    alert(message);
    return;
  }

  // Map 'error' to 'danger' for Bootstrap styling
  const bsType = type === "error" ? "danger" : type;
  const icon =
    {
      success: "bi-check-circle-fill",
      danger: "bi-exclamation-circle-fill",
      warning: "bi-exclamation-triangle-fill",
      info: "bi-info-circle-fill",
    }[bsType] || "bi-info-circle-fill";

  const toastId = "toast-" + Date.now();
  const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${bsType} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

  container.insertAdjacentHTML("beforeend", toastHTML);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, { delay: 10000 });
  toast.show();

  // Remove from DOM after hide
  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove();
  });
};

/**
 * Auto-show alerts based on URL parameters
 * Example: ?msg=Success&type=success or ?error=Some+Error
 */
window.addEventListener("load", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const msg = urlParams.get("msg");
  const type = urlParams.get("type") || "success";
  const error = urlParams.get("error");
  const success = urlParams.get("success");

  if (msg) {
    showAlert(msg, type);
  } else if (error) {
    showAlert(error, "error");
  } else if (success === "1") {
    showAlert("Operation completed successfully!", "success");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  // 1. Synchronize AOS with the Custom Loader
  // We wait for the 'pageLoaded' event from loader.php to initialize AOS
  // This ensures elements don't animate while the loader is still visible
  document.addEventListener("pageLoaded", () => {
    setTimeout(() => {
      AOS.init({
        duration: 600,
        easing: "ease-in-out",
        once: true,
        offset: 40,
        delay: 50,
      });
    }, 600);
  });

  // 2. Go to Top Button Logic
  const scrollTopBtn = document.getElementById("scrollTopBtn");

  if (scrollTopBtn) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 300) {
        scrollTopBtn.classList.add("visible");
      } else {
        scrollTopBtn.classList.remove("visible");
      }
    });

    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }
});

const currentTitle = document.title;
const onleaveTitle = "Go Back to website";

function changeTitle() {
  window.onblur = () => {
    document.title = onleaveTitle;
  };
  window.onfocus = () => {
    document.title = currentTitle;
  };
}

changeTitle();

// Theme Toggle Logic
document.addEventListener("DOMContentLoaded", () => {
  const themeToggleBtn = document.getElementById("themeToggle");
  const icon = themeToggleBtn?.querySelector("i");
  const html = document.documentElement;

  // Check LocalStorage
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme) {
    html.setAttribute("data-theme", savedTheme);
    updateIcon(savedTheme);
  } else {
    // System preference check could go here
  }

  // Toggle Function
  window.toggleTheme = function () {
    const currentTheme = html.getAttribute("data-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";

    html.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateIcon(newTheme);
  };

  function updateIcon(theme) {
    if (!icon) return;
    if (theme === "dark") {
      icon.classList.remove("bi-moon-fill");
      icon.classList.add("bi-sun-fill");
    } else {
      icon.classList.remove("bi-sun-fill");
      icon.classList.add("bi-moon-fill");
    }
  }
});
