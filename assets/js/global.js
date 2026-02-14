
(function () {
  const pathParts = window.location.pathname.split("/");
  const siteRoot = pathParts.includes("redhope") ? "/redhope/" : "/";

  const currentPath = window.location.pathname;
  const isLoginPage = currentPath.endsWith("login.php");
  const isProtectedPath =
    currentPath.includes("/dashboard/") || currentPath.includes("/admin/");
  const storedToken = localStorage.getItem("redhope_jwt");

  console.log("🛡️ Auth Gate Check:", {
    sessionActive: window.PHP_SESSION_ACTIVE,
    hasToken: !!storedToken,
    path: currentPath,
  });

  if (!window.PHP_SESSION_ACTIVE && storedToken) {
    console.log("🔄 Attempting to restore session from JWT...");

    fetch(siteRoot + "apis/restore_session.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ token: storedToken }),
    })
      .then((res) => res.json())
      .then((data) => {
        console.log("📥 Restore Response:", data);
        if (data.success) {
          console.log("✅ Session restored successfully.");
          if (isLoginPage) {
            window.location.href = siteRoot + "dashboard.php";
          } else {
            window.location.reload();
          }
        } else {
          console.warn("❌ Token restoration failed:", data.message);
          localStorage.removeItem("redhope_jwt");
          if (isProtectedPath) {
            window.location.href = siteRoot + "login.php?error=Session+Expired";
          }
        }
      })
      .catch((err) => {
        console.error("⚠️ Auth Gate Fetch Error:", err);
      });
  }
  else if (!window.PHP_SESSION_ACTIVE && !storedToken && isProtectedPath) {
    console.warn("🚫 Access denied. No session or token found.");
    window.location.href = siteRoot + "login.php?error=Authentication+Required";
  }

  else if (window.PHP_SESSION_ACTIVE && !storedToken) {
    console.log("🔄 Session active but token missing. Syncing...");
    fetch(siteRoot + "apis/get_current_token.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.token) {
          localStorage.setItem("redhope_jwt", data.token);
          console.log("✅ Token synced to localStorage.");
        }
      })
      .catch((err) => console.error("⚠️ Token sync failed:", err));
  }
})();

window.logoutUser = function () {
  localStorage.removeItem("redhope_jwt");
  const pathParts = window.location.pathname.split("/");
  const siteRoot = pathParts.includes("redhope") ? "/redhope/" : "/";
  window.location.href = siteRoot + "apis/logout.php";
};


document.addEventListener("DOMContentLoaded", () => {
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

const originalAlert = window.alert;
window.showAlert = function (message, type = "success") {
  const container = document.getElementById("alertContainer");
  if (!container) {
    console.warn("Alert container not found! Falling back to standard alert.");
    originalAlert(message);
    return;
  }

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
  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove();
  });
};


window.alert = function (message) {
  showAlert(message, "warning"); 
};

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

document.addEventListener("DOMContentLoaded", () => {
  const themeToggleBtn = document.getElementById("themeToggle");
  const icon = themeToggleBtn?.querySelector("i");
  const html = document.documentElement;

  const savedTheme = localStorage.getItem("theme");
  if (savedTheme) {
    html.setAttribute("data-theme", savedTheme);
    updateIcon(savedTheme);
  } else {
    
  }

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
