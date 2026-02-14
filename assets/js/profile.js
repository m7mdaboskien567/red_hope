document.addEventListener("DOMContentLoaded", () => {
  initPersonalInfoForm();
  initDonorProfileForm();
  initPasswordForm();
  initAppointmentForm();
  initCancelButtons();
  initDonationsFilter();
  initCenterPicker();
  initLocationAwareness();
});

function initPersonalInfoForm() {
  const form = document.getElementById("personalInfoForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Saving...';
    btn.disabled = true;

    const formData = {
      first_name: form.first_name.value,
      last_name: form.last_name.value,
      email: form.email.value,
      phone: form.phone.value,
    };

    try {
      const response = await fetch("/redhope/apis/update_donor_profile.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        showAlert(result.message, "success");
      } else {
        showAlert(result.message, "error");
      }
    } catch (error) {
      showAlert("An error occurred. Please try again.", "error");
    } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
}

function initDonorProfileForm() {
  const form = document.getElementById("donorProfileForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Updating...';
    btn.disabled = true;

    const formData = {
      weight_kg: parseFloat(form.weight_kg.value),
      medical_conditions: form.medical_conditions.value,
      is_anonymous: form.is_anonymous.checked,
    };

    try {
      const response = await fetch("/redhope/apis/update_donor_profile.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        showAlert(result.message, "success");
      } else {
        showAlert(result.message, "error");
      }
    } catch (error) {
      showAlert("An error occurred. Please try again.", "error");
    } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
}

function initPasswordForm() {
  const form = document.getElementById("passwordForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;

    if (form.new_password.value !== form.confirm_password.value) {
      showAlert("New passwords do not match.", "error");
      return;
    }

    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Changing...';
    btn.disabled = true;

    const formData = {
      current_password: form.current_password.value,
      new_password: form.new_password.value,
      confirm_password: form.confirm_password.value,
    };

    try {
      const response = await fetch("/redhope/apis/change_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        showAlert(result.message, "success");
        form.reset();
      } else {
        showAlert(result.message, "error");
      }
    } catch (error) {
      showAlert("An error occurred. Please try again.", "error");
    } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
}

function initAppointmentForm() {
  const form = document.getElementById("appointmentForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Scheduling...';
    btn.disabled = true;

    const formData = {
      center_id: form.center_id.value,
      appointment_date: form.appointment_date.value,
      appointment_time: form.appointment_time.value,
      notes: form.notes.value,
    };

    try {
      const response = await fetch("/redhope/apis/create_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        window.location.href = `/redhope/dashboard/donator/index.php?msg=${encodeURIComponent(result.message)}&type=success`;
      } else {
        window.location.href = `/redhope/dashboard/donator/index.php?msg=${encodeURIComponent(result.message)}&type=error`;
      }
    } catch (error) {
      window.location.href = `/redhope/dashboard/donator/index.php?msg=${encodeURIComponent("An error occurred. Please try again.")}&type=error`;
    }
  });
}

function initCancelButtons() {
  document.querySelectorAll(".btn-cancel, .btn-cancel-apt").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const appointmentId = btn.dataset.id;

      if (!confirm("Are you sure you want to cancel this appointment?")) {
        return;
      }

      btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
      btn.disabled = true;

      try {
        const response = await fetch("/redhope/apis/cancel_appointment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ appointment_id: appointmentId }),
        });

        const result = await response.json();

        if (result.success) {
          location.reload();
        } else {
          showAlert(result.message, "error");
          btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel';
          btn.disabled = false;
        }
      } catch (error) {
        showAlert("An error occurred. Please try again.", "error");
        btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel';
        btn.disabled = false;
      }
    });
  });
}

window.openRescheduleModal = function (id, date, time) {
  document.getElementById("reschedule_appt_id").value = id;
  document.getElementById("reschedule_date").value = date;
  document.getElementById("reschedule_time").value = time;

  const modal = new bootstrap.Modal(document.getElementById("rescheduleModal"));
  modal.show();
};

const rescheduleForm = document.getElementById("rescheduleForm");
if (rescheduleForm) {
  rescheduleForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = rescheduleForm.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm"></span> Saving...';
    btn.disabled = true;

    const formData = {
      appointment_id: document.getElementById("reschedule_appt_id").value,
      appointment_date: document.getElementById("reschedule_date").value,
      appointment_time: document.getElementById("reschedule_time").value,
    };

    try {
      const response = await fetch("/redhope/apis/reschedule_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const result = await response.json();
      if (result.success) {
        window.location.href = `/redhope/dashboard/donator/index.php?msg=${encodeURIComponent(result.message)}&type=success`;
      } else {
        showAlert(result.message, "error");
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    } catch (error) {
      showAlert("An error occurred. Please try again.", "error");
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
}

window.confirmCancelAppointment = function (id) {
  if (
    confirm(
      "Are you sure you want to cancel this appointment? This action cannot be undone.",
    )
  ) {
    cancelAppointment(id);
  }
};

async function cancelAppointment(id) {
  try {
    const response = await fetch("/redhope/apis/cancel_appointment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ appointment_id: id }),
    });

    const result = await response.json();
    if (result.success) {
      removeRow(`appt-card-${id}`);
      showAlert(result.message, "success");
    } else {
      showAlert(result.message, "error");
    }
  } catch (error) {
    showAlert("An error occurred. Please try again.", "error");
  }
}

window.acceptBloodRequest = async function (requestId) {
  if (
    !confirm(
      "Are you sure you want to commit to this blood request? Click OK only if you intend to visit the hospital to donate.",
    )
  ) {
    return;
  }

  const btn = event.currentTarget;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Processing...';
  btn.disabled = true;

  try {
    const response = await fetch("/redhope/apis/accept_blood_request.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ request_id: requestId }),
    });

    const result = await response.json();

    if (result.success) {
      document.getElementById("h_name").innerText = result.hospital.name;
      document.getElementById("h_address").innerText = result.hospital.address;
      document.getElementById("h_city").innerText = result.hospital.city;
      document.getElementById("h_phone").innerHTML =
        `<i class="bi bi-telephone"></i> ${result.hospital.contact_number}`;
      document.getElementById("h_email").innerHTML =
        `<i class="bi bi-envelope"></i> ${result.hospital.email}`;

      const infoModal = new bootstrap.Modal(
        document.getElementById("hospitalInfoModal"),
      );
      infoModal.show();

      showAlert(result.message, "success");

      document.getElementById("hospitalInfoModal").addEventListener(
        "hidden.bs.modal",
        function () {
          const row = document.getElementById(`row-request-${requestId}`);
          if (row) {
            const statusBadge = row.querySelector(".status-badge");
            const urgencyBadge = row.querySelector(".activity-status");
            const btn = row.querySelector("button");

            if (statusBadge) {
              statusBadge.textContent = "In Progress";
              statusBadge.className = "status-badge in-progress";
            }
            if (btn) {
              const span = document.createElement("span");
              span.className = "text-muted small italic";
              span.textContent = "Donor is on the way";
              btn.replaceWith(span);
            }
          }
        },
        { once: true },
      );
    } else {
      showAlert(result.message, "error");
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  } catch (error) {
    showAlert("An error occurred. Please try again.", "error");
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
};

function initDonationsFilter() {
  const searchInput = document.getElementById("donationsSearch");
  const table = document.querySelector(".donations-table");
  if (!searchInput || !table) return;

  searchInput.addEventListener("keyup", () => {
    const query = searchInput.value.toLowerCase();
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      if (row.cells.length === 1) return;

      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });
}

window.completeBloodRequest = async function (requestId) {
  if (
    !confirm(
      "Have you successfully fulfilled this donation? Click OK to mark it as Fulfilled.",
    )
  ) {
    return;
  }

  const btn = event.currentTarget;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Saving...';
  btn.disabled = true;

  try {
    const response = await fetch("/redhope/apis/complete_blood_request.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ request_id: requestId }),
    });

    const result = await response.json();

    if (result.success) {
      showAlert(result.message, "success");
      removeRow(`row-request-${requestId}`);
    } else {
      showAlert(result.message, "error");
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  } catch (error) {
    showAlert("An error occurred. Please try again.", "error");
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
};

const style = document.createElement("style");
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
`;
document.head.appendChild(style);

window.viewMessage = function (subject, sender, date, content) {
  document.getElementById("view_msg_subject").textContent = subject;
  document.getElementById("view_msg_sender").textContent = sender;
  document.getElementById("view_msg_date").textContent = date;
  document.getElementById("view_msg_content").textContent = content;

  const el = document.getElementById("viewMessageModal");
  if (el) {
    const modal = new bootstrap.Modal(el);
    modal.show();
  }
};

/**
 * Center Picker UI Logic
 */
function initCenterPicker() {
  const select = document.getElementById("center_id_select");
  if (!select) return;

  // Additional select interaction logic if needed
}

/**
 * Location Resilience & Sorting Logic
 */
function initLocationAwareness() {
  const btn = document.getElementById("btn-detect-location");
  if (!btn) return;

  btn.addEventListener("click", async () => {
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Locating...';
    btn.disabled = true;

    try {
      const coords = await LocationService.getCurrentPosition();
      updateDistancesAndSort(coords);
      showAlert("Location detected! Nearest centers highlighted.", "success");
    } catch (error) {
      console.error("Location error:", error);
      showAlert("Could not detect location: " + error.message, "error");
    } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });

  // Listen for section loaded to refresh recommendations
  document.addEventListener("sectionLoaded", (e) => {
    if (e.detail.section === "find-center") {
      refreshRecommendations();
    }
  });
}

async function updateDistancesAndSort(userCoords) {
  const select = document.getElementById("center_id_select");
  if (!select) return;

  const options = Array.from(select.options).filter((opt) => opt.value);
  const data = options.map((opt) => ({
    element: opt,
    id: opt.value,
    name: opt.text.split(" (")[0], // Extract pure name
    city: opt.dataset.city || "",
    lat: parseFloat(opt.dataset.lat),
    lng: parseFloat(opt.dataset.lng),
  }));

  const sorted = LocationService.sortCentersByProximity(data, userCoords);

  // Clear existing options (except the first disabled one)
  while (select.options.length > 1) {
    select.remove(1);
  }

  // Re-add sorted options with distance labels
  sorted.forEach((s, index) => {
    const newOpt = s.element;
    const distanceText = `${s.distance.toFixed(1)}km away`;
    newOpt.text = `${s.name} (${s.city}) - ${distanceText}`;
    if (index === 0) {
      newOpt.text = `⭐ RECOMMENDED: ${newOpt.text}`;
    }
    select.add(newOpt);
  });

  // Auto-select the first (nearest) option
  if (sorted.length > 0) {
    select.value = sorted[0].id;
    select.dispatchEvent(new Event("change"));
  }
}

async function refreshRecommendations() {
  const list = document.getElementById("recommended-list");
  if (!list) return;

  try {
    const coords = await LocationService.getCurrentPosition();

    // Fetch centers from select options
    const options = Array.from(
      document.querySelectorAll("#center_id_select option"),
    ).filter((opt) => opt.value);
    const centers = options.map((opt) => ({
      center_id: opt.value,
      name: opt.text.split(" (")[0].replace("⭐ RECOMMENDED: ", ""),
      city: opt.dataset.city || "",
      lat: parseFloat(opt.dataset.lat),
      lng: parseFloat(opt.dataset.lng),
    }));

    const sorted = LocationService.sortCentersByProximity(centers, coords);

    list.innerHTML = sorted
      .map(
        (s, index) => `
      <div class="col-md-6 col-lg-4">
        <div class="recommended-card h-100">
          <div class="recommended-card-header">
            ${index === 0 ? '<span class="recommended-badge">Nearest To You</span>' : ""}
            <i class="bi bi-geo-alt-fill opacity-50"></i>
            <h4 class="h5 mb-0 mt-2">${s.name}</h4>
          </div>
          <div class="recommended-card-body">
            <div class="d-flex align-items-baseline gap-2 mb-3">
              <span class="dist-value">${s.distance.toFixed(1)}</span>
              <span class="dist-unit">kilometers away</span>
            </div>
            <p class="text-muted small mb-0">
              <i class="bi bi-map"></i> Located in ${s.city}<br>
              <i class="bi bi-clock"></i> Open until 8:00 PM
            </p>
          </div>
          <div class="recommended-card-footer">
            <button class="btn btn-sm btn-link text-danger p-0 fw-bold" onclick="selectAndGoToDonate(${s.center_id})">
              Book Here <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    `,
      )
      .join("");
  } catch (error) {
    list.innerHTML = `
      <div class="col-12 text-center py-4">
        <div class="alert alert-warning d-inline-block">
          <i class="bi bi-geo-alt-fill"></i> Please enable location access to see recommendations.
        </div>
      </div>
    `;
  }
}

window.selectAndGoToDonate = function (id) {
  const select = document.getElementById("center_id_select");
  if (select) {
    select.value = id;
    select.dispatchEvent(new Event("change"));
    loadSection("donate");
    // Scroll to form
    document
      .getElementById("appointmentForm")
      .scrollIntoView({ behavior: "smooth" });
  }
};

function removeRow(rowId) {
  const row = document.getElementById(rowId);
  if (row) {
    row.style.transition = "all 0.4s ease";
    row.style.opacity = "0";
    row.style.transform = "translateX(20px)";
    setTimeout(() => {
      row.remove();
    }, 400);
  }
}
