document.addEventListener("DOMContentLoaded", () => {
  initPersonalInfoForm();
  initDonorProfileForm();
  initPasswordForm();
  initAppointmentForm();
  initCancelButtons();
  initDonationsFilter();
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
      window.location.href = `/redhope/dashboard/donator/index.php?msg=${encodeURIComponent(result.message)}&type=success`;
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
          location.reload();
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
      setTimeout(() => location.reload(), 1500);
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
