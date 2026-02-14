function openModal(type, data = null) {
  const modal = document.getElementById("adminModal");
  const title = document.getElementById("adminModalLabel");
  const body = document.getElementById("adminModalBody");
  const saveBtn = document.getElementById("adminModalSave");

  const isEdit = data !== null;
  let html = "";

  switch (type) {
    case "user":
      title.textContent = isEdit ? "Edit User" : "Add User";
      html = `
                <input type="hidden" id="modal_user_id" value="${isEdit ? data.user_id : ""}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" id="modal_first_name" class="form-control" value="${isEdit ? data.first_name : ""}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="modal_last_name" class="form-control" value="${isEdit ? data.last_name : ""}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="modal_email" class="form-control" value="${isEdit ? data.email : ""}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" id="modal_phone" class="form-control" value="${isEdit ? data.phone || "" : ""}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>
                        <select id="modal_role" class="form-select">
                            <option value="Donor" ${isEdit && data.role === "Donor" ? "selected" : ""}>Donor</option>
                            <option value="Hospital Admin" ${isEdit && data.role === "Hospital Admin" ? "selected" : ""}>Hospital Admin</option>
                            <option value="Super Admin" ${isEdit && data.role === "Super Admin" ? "selected" : ""}>Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gender</label>
                        <select id="modal_gender" class="form-select">
                            <option value="Male" ${isEdit && data.gender === "Male" ? "selected" : ""}>Male</option>
                            <option value="Female" ${isEdit && data.gender === "Female" ? "selected" : ""}>Female</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" id="modal_dob" class="form-control" value="${isEdit ? data.date_of_birth || "" : ""}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password${isEdit ? " (leave blank to keep current)" : ""}</label>
                        <input type="password" id="modal_password" class="form-control" ${isEdit ? "" : "required"}>
                    </div>
                </div>
            `;
      saveBtn.onclick = () => saveUser(isEdit);
      break;

    case "hospital":
      title.textContent = isEdit ? "Edit Hospital" : "Add Hospital";
      html = `
                <input type="hidden" id="modal_hospital_id" value="${isEdit ? data.hospital_id : ""}">
                <div class="mb-3">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" id="modal_hosp_name" class="form-control" value="${isEdit ? data.name : ""}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea id="modal_hosp_address" class="form-control" rows="2" required>${isEdit ? data.address : ""}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" id="modal_hosp_city" class="form-control" value="${isEdit ? data.city : ""}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" id="modal_hosp_contact" class="form-control" value="${isEdit ? data.contact_number || "" : ""}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="modal_hosp_email" class="form-control" value="${isEdit ? data.email || "" : ""}">
                </div>
            `;
      saveBtn.onclick = () => saveHospital(isEdit);
      break;

    case "center":
      title.textContent = isEdit ? "Edit Blood Center" : "Add Blood Center";
      html = `
                <input type="hidden" id="modal_center_id" value="${isEdit ? data.center_id : ""}">
                <div class="mb-3">
                    <label class="form-label">Center Name</label>
                    <input type="text" id="modal_center_name" class="form-control" value="${isEdit ? data.name : ""}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea id="modal_center_address" class="form-control" rows="2" required>${isEdit ? data.address : ""}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" id="modal_center_city" class="form-control" value="${isEdit ? data.city : ""}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" id="modal_center_contact" class="form-control" value="${isEdit ? data.contact_number || "" : ""}">
                </div>
            `;
      saveBtn.onclick = () => saveCenter(isEdit);
      break;

    case "map_center":
      title.textContent = isEdit
        ? "Edit Map Center (JSON)"
        : "Add Map Center (JSON)";
      html = `
                <input type="hidden" id="modal_json_id" value="${isEdit ? data.center_id : ""}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Center Name</label>
                        <input type="text" id="modal_json_name" class="form-control" value="${isEdit ? data.name : ""}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" id="modal_json_contact" class="form-control" value="${isEdit ? data.contact_number || "" : ""}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" id="modal_json_address" class="form-control" value="${isEdit ? data.address : ""}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" id="modal_json_city" class="form-control" value="${isEdit ? data.city : ""}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Maps Link (Auto-Fill)</label>
                    <div class="input-group">
                        <input type="text" id="modal_json_link" class="form-control" placeholder="https://www.google.com/maps/place/...">
                        <button class="btn btn-outline-primary" type="button" onclick="autoFillFromMapLink(this)">
                            <i class="bi bi-magic"></i> Auto-Fill
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" id="modal_json_lat" class="form-control" value="${isEdit ? data.lat : ""}" placeholder="e.g. 30.0444" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" id="modal_json_lng" class="form-control" value="${isEdit ? data.lng : ""}" placeholder="e.g. 31.2357" required>
                    </div>
                </div>
            `;
      saveBtn.onclick = () => saveMapCenter(isEdit);
      break;

    case "inventory":
      title.textContent = isEdit ? "Edit Inventory Item" : "Add Inventory Item";
      const centerOpts = (window.centersData || [])
        .map(
          (c) =>
            `<option value="${c.center_id}" ${isEdit && data.current_location_id == c.center_id ? "selected" : ""}>${c.name} - ${c.city}</option>`,
        )
        .join("");
      const donationOpts = (window.donationsData || [])
        .map(
          (d) =>
            `<option value="${d.donation_id}">#${d.donation_id} — ${d.donor_name} (${d.blood_type}, ${d.donated_at})</option>`,
        )
        .join("");
      html = `
                <input type="hidden" id="modal_inventory_id" value="${isEdit ? data.inventory_id : ""}">
                ${
                  !isEdit
                    ? `
                <div class="mb-3">
                    <label class="form-label">Donation</label>
                    <select id="modal_inv_donation" class="form-select" required>
                        <option value="">-- Select a Donation --</option>
                        ${donationOpts}
                    </select>
                </div>`
                    : ""
                }
                <div class="mb-3">
                    <label class="form-label">Blood Type</label>
                    <select id="modal_inv_blood" class="form-select">
                        ${["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"]
                          .map(
                            (bt) =>
                              `<option value="${bt}" ${isEdit && data.blood_type === bt ? "selected" : ""}>${bt}</option>`,
                          )
                          .join("")}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" id="modal_inv_expiry" class="form-control" value="${isEdit ? data.expiry_date : ""}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location (Center)</label>
                    <select id="modal_inv_location" class="form-select">
                        ${centerOpts}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select id="modal_inv_status" class="form-select">
                        ${[
                          "Available",
                          "Reserved",
                          "Dispatched",
                          "Expired",
                          "Discarded",
                        ]
                          .map(
                            (s) =>
                              `<option value="${s}" ${isEdit && data.status === s ? "selected" : ""}>${s}</option>`,
                          )
                          .join("")}
                    </select>
                </div>
            `;
      saveBtn.onclick = () => saveInventory(isEdit);
      break;
  }

  body.innerHTML = html;
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

async function saveUser(isEdit) {
  const payload = {
    action: isEdit ? "update" : "create",
    user_id: document.getElementById("modal_user_id").value,
    first_name: document.getElementById("modal_first_name").value,
    last_name: document.getElementById("modal_last_name").value,
    email: document.getElementById("modal_email").value,
    phone: document.getElementById("modal_phone").value,
    role: document.getElementById("modal_role").value,
    gender: document.getElementById("modal_gender").value,
    date_of_birth: document.getElementById("modal_dob").value,
    password: document.getElementById("modal_password").value,
  };

  try {
    const res = await fetch("/redhope/apis/admin/manage_users.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 1200);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function saveHospital(isEdit) {
  const payload = {
    action: isEdit ? "update" : "create",
    hospital_id: document.getElementById("modal_hospital_id").value,
    name: document.getElementById("modal_hosp_name").value,
    address: document.getElementById("modal_hosp_address").value,
    city: document.getElementById("modal_hosp_city").value,
    contact_number: document.getElementById("modal_hosp_contact").value,
    email: document.getElementById("modal_hosp_email").value,
  };

  try {
    const res = await fetch("/redhope/apis/admin/manage_hospitals.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 1200);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function saveCenter(isEdit) {
  const payload = {
    action: isEdit ? "update" : "create",
    center_id: document.getElementById("modal_center_id").value,
    name: document.getElementById("modal_center_name").value,
    address: document.getElementById("modal_center_address").value,
    city: document.getElementById("modal_center_city").value,
    contact_number: document.getElementById("modal_center_contact").value,
  };

  try {
    const res = await fetch("/redhope/apis/admin/manage_center.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 1200);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function saveInventory(isEdit) {
  const payload = {
    action: isEdit ? "update" : "create",
    inventory_id: document.getElementById("modal_inventory_id").value,
    blood_type: document.getElementById("modal_inv_blood").value,
    expiry_date: document.getElementById("modal_inv_expiry").value,
    current_location_id: document.getElementById("modal_inv_location").value,
    status: document.getElementById("modal_inv_status").value,
  };
  if (!isEdit) {
    payload.donation_id = document.getElementById("modal_inv_donation").value;
  }

  try {
    const res = await fetch("/redhope/apis/admin/manage_inventory.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 1200);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function autoFillFromMapLink(btn) {
  const input = document.getElementById("modal_json_link");
  const url = input.value.trim();
  if (!url) {
    showAlert("Please paste a Google Maps link first", "error");
    return;
  }

  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  try {
    const res = await fetch("/redhope/apis/admin/resolve_map_link.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ url: url }),
    });
    const data = await res.json();

    if (data.success) {
      document.getElementById("modal_json_lat").value = data.lat;
      document.getElementById("modal_json_lng").value = data.lng;
      showAlert("Coordinates extracted!", "success");
    } else {
      showAlert(data.message || "Could not extract coordinates", "error");
    }
  } catch (e) {
    showAlert("Error resolving link", "error");
  } finally {
    btn.disabled = false;
    btn.innerHTML = origHtml;
  }
}

async function saveMapCenter(isEdit = false) {
  const payload = {
    action: isEdit ? "update" : "create",
    center_id: isEdit ? document.getElementById("modal_json_id").value : null,
    name: document.getElementById("modal_json_name").value,
    address: document.getElementById("modal_json_address").value,
    city: document.getElementById("modal_json_city").value,
    lat: document.getElementById("modal_json_lat").value,
    lng: document.getElementById("modal_json_lng").value,
    contact_number: document.getElementById("modal_json_contact").value,
  };

  if (!payload.name || !payload.lat || !payload.lng) {
    showAlert("Please fill in all required fields (Name, Lat, Lng)", "error");
    return;
  }

  try {
    const res = await fetch("/redhope/apis/admin/manage_json_centers.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 1200);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function deleteMapCenter(centerId) {
  if (!confirm("Are you sure you want to delete this map center?")) return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_json_centers.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", center_id: centerId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("Map center deleted successfully", "success");
      removeRow(`row-map-center-${centerId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function deleteUser(userId) {
  if (
    !confirm(
      "Are you sure you want to delete this user? This action cannot be undone.",
    )
  )
    return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_users.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", user_id: userId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("User deleted successfully", "success");
      removeRow(`row-user-${userId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function deleteHospital(hospitalId) {
  if (
    !confirm(
      "Are you sure you want to delete this hospital? This action cannot be undone.",
    )
  )
    return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_hospitals.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", hospital_id: hospitalId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("Hospital deleted successfully", "success");
      removeRow(`row-hospital-${hospitalId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function verifyHospital(hospitalId, btn) {
  if (!confirm("Are you sure you want to verify this hospital?")) return;
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
  btn.disabled = true;
  try {
    const res = await fetch("/redhope/apis/admin/manage_hospitals.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "verify", hospital_id: hospitalId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("Hospital verified successfully", "success");
      const row = document.getElementById(`row-hospital-${hospitalId}`);
      if (row) {
        const statusCell = row.querySelector("td:nth-child(5)");
        if (statusCell) {
          statusCell.innerHTML =
            '<span class="status-badge approved"><i class="bi bi-check-circle-fill"></i> Verified</span>';
        }
        btn.remove();
      }
    } else {
      showAlert(result.message, "error");
      btn.innerHTML = orig;
      btn.disabled = false;
    }
  } catch (e) {
    showAlert("An error occurred", "error");
    btn.innerHTML = orig;
    btn.disabled = false;
  }
}

async function deleteCenter(centerId) {
  if (!confirm("Are you sure you want to delete this blood center?")) return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_center.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", center_id: centerId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("Blood center deleted successfully", "success");
      removeRow(`row-center-${centerId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function deleteInventory(inventoryId) {
  if (!confirm("Are you sure you want to delete this inventory item?")) return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_inventory.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", inventory_id: inventoryId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert("Inventory item deleted successfully", "success");
      removeRow(`row-inventory-${inventoryId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

async function updateAppointment(appointmentId, action, actionLabel) {
  if (!confirm(`Are you sure you want to ${actionLabel} this appointment?`))
    return;
  try {
    const res = await fetch("/redhope/apis/admin/manage_appointments.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: action, appointment_id: appointmentId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      const row = document.getElementById(`row-appointment-${appointmentId}`);
      if (row) {
        const statusCell = row.querySelector("td:nth-child(6)");
        const actionsCell = row.querySelector("td:nth-child(7)");
        if (statusCell) {
          let statusLabel =
            action === "approve"
              ? "Allowed"
              : action === "reject"
                ? "Rejected"
                : action === "complete"
                  ? "Completed"
                  : "Pending";
          let badgeClass = statusLabel.toLowerCase();
          statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${statusLabel}</span>`;
        }
        if (action === "complete" && actionsCell) {
          actionsCell.innerHTML =
            '<span class="text-muted small"><i class="bi bi-check-circle-fill text-success"></i> Done</span>';
        } else {
          // For other status changes, we'll reload for now to ensure all UI logic (buttons) is correct
          setTimeout(() => location.reload(), 1200);
        }
      }
    } else {
      showAlert(result.message, "error");
    }
  } catch (e) {
    showAlert("An error occurred", "error");
  }
}

function openComposeModal() {
  const el = document.getElementById("composeModal");
  if (el) {
    const modal = new bootstrap.Modal(el);
    modal.show();
  }
}

function viewMessage(subject, sender, receiver, date, content) {
  const subjEl = document.getElementById("view_msg_subject");
  const sendEl = document.getElementById("view_msg_sender");
  const dateEl = document.getElementById("view_msg_date");
  const contEl = document.getElementById("view_msg_content");

  if (subjEl) subjEl.textContent = subject;
  if (sendEl) sendEl.textContent = sender;
  if (dateEl) dateEl.textContent = date;
  if (contEl) contEl.textContent = content;

  const el = document.getElementById("viewMessageModal");
  if (el) {
    const modal = new bootstrap.Modal(el);
    modal.show();
  }
}

function replyMessage(senderId, subject) {
  const receiverSelect = document.getElementById("msg_receiver");
  const subjectInput = document.getElementById("msg_subject");

  if (receiverSelect) receiverSelect.value = senderId;
  if (subjectInput) subjectInput.value = subject;

  openComposeModal();
}

async function deleteMessage(messageId) {
  if (!confirm("Are you sure you want to delete this message?")) return;

  try {
    const res = await fetch("/redhope/apis/admin/manage_messages.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", message_id: messageId }),
    });
    const result = await res.json();
    if (result.success) {
      showAlert(result.message, "success");
      removeRow(`row-message-${messageId}`);
    } else {
      showAlert(result.message, "error");
    }
  } catch (error) {
    showAlert("An error occurred while deleting the message", "error");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const composeForm = document.getElementById("composeForm");
  if (composeForm) {
    composeForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const receiverId = document.getElementById("msg_receiver").value;
      const subject = document.getElementById("msg_subject").value;
      const content = document.getElementById("msg_content").value;
      const btn = composeForm.querySelector('button[type="submit"]');

      if (!receiverId || !content) {
        showAlert("Please fill in all required fields", "error");
        return;
      }

      const origText = btn.innerHTML;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Sending...';
      btn.disabled = true;

      try {
        const res = await fetch("/redhope/apis/send_message.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            receiver_id: receiverId,
            subject: subject,
            message_content: content,
          }),
        });
        const result = await res.json();
        if (result.success) {
          showAlert(result.message, "success");
          setTimeout(() => location.reload(), 1200);
        } else {
          showAlert(result.message, "error");
          btn.innerHTML = origText;
          btn.disabled = false;
        }
      } catch (error) {
        showAlert("An error occurred while sending the message", "error");
        btn.innerHTML = origText;
        btn.disabled = false;
      }
    });
  }
});

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
