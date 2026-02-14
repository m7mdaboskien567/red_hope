document.addEventListener("DOMContentLoaded", function () {
  loadAIDocs();

  const uploadZone = document.getElementById("uploadZone");
  const fileInput = document.getElementById("aiDocUpload");

  uploadZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadZone.classList.add("drag-over");
  });

  uploadZone.addEventListener("dragleave", () => {
    uploadZone.classList.remove("drag-over");
  });

  uploadZone.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadZone.classList.remove("drag-over");
    const files = e.dataTransfer.files;
    handleFiles(files);
  });

  fileInput.addEventListener("change", (e) => {
    handleFiles(e.target.files);
  });
  uploadZone.addEventListener("click", () => {
    fileInput.click();
  });
});

function handleFiles(files) {
  if (files.length === 0) return;

  const formData = new FormData();
  let validFiles = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (file.name.toLowerCase().endsWith(".txt")) {
      formData.append("file", file);
      validFiles++;
    } else {
      showToast("Only .txt files are allowed", "error");
    }
  }

  if (validFiles > 0) {
    formData.append("action", "upload");
    uploadFile(formData);
  }
}

function uploadFile(formData) {
  const uploadZone = document.getElementById("uploadZone");
  uploadZone.innerHTML =
    '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Uploading...</p>';

  fetch("../assets/uploads/files_manager.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("File uploaded successfully!", "success");
        loadAIDocs();
      } else {
        showToast(data.error || "Upload failed", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showToast("An error occurred during upload", "error");
    })
    .finally(() => {
      uploadZone.innerHTML = `
            <i class="bi bi-cloud-arrow-up"></i>
            <h3>Upload Training Data</h3>
            <p>Drag and drop TXT files to feed HopeAI's knowledge base.</p>
        `;
    });
}

function loadAIDocs() {
  const tableBody = document.getElementById("aiDocsTableBody");
  if (!tableBody) return;

  tableBody.innerHTML =
    '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

  fetch("../assets/uploads/files_manager.php?action=list")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderDocsTable(data.files);
      } else {
        tableBody.innerHTML =
          '<tr><td colspan="5" class="text-center text-danger">Failed to load documents</td></tr>';
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      tableBody.innerHTML =
        '<tr><td colspan="5" class="text-center text-danger">Error loading documents</td></tr>';
    });
}

function renderDocsTable(files) {
  const tableBody = document.getElementById("aiDocsTableBody");
  tableBody.innerHTML = "";

  if (files.length === 0) {
    tableBody.innerHTML =
      '<tr><td colspan="5" class="text-center text-muted">No documents found. Upload a .txt file to get started.</td></tr>';
    return;
  }

  files.forEach((file) => {
    const row = document.createElement("tr");
    const statusClass = file.status === "active" ? "active" : "inactive";
    const statusBadge =
      file.status === "active"
        ? '<span class="doc-status-badge active">Active</span>'
        : '<span class="doc-status-badge">Inactive</span>';

    const sizeKB = (file.size / 1024).toFixed(2) + " KB";
    const date = new Date(file.uploaded_at).toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
    });

    row.innerHTML = `
            <td><strong>${file.original_name}</strong></td>
            <td>${sizeKB}</td>
            <td>${date}</td>
            <td>${statusBadge}</td>
            <td>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm ${file.status === "active" ? "btn-outline-warning" : "btn-outline-success"}" 
                            onclick="toggleDocStatus('${file.id}')" 
                            title="${file.status === "active" ? "Deactivate" : "Activate"}">
                        <i class="bi ${file.status === "active" ? "bi-pause-circle" : "bi-play-circle"}"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteDoc('${file.id}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    tableBody.appendChild(row);
  });

  updateStats(files);
}

function toggleDocStatus(id) {
  const formData = new FormData();
  formData.append("action", "toggle_status");
  formData.append("id", id);

  fetch("../assets/uploads/files_manager.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Document status updated", "success");
        loadAIDocs();
      } else {
        showToast(data.error || "Failed to update status", "error");
      }
    });
}

function deleteDoc(id) {
  if (!confirm("Are you sure you want to delete this document?")) return;

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", id);

  fetch("../assets/uploads/files_manager.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Document deleted", "success");
        loadAIDocs();
      } else {
        showToast(data.error || "Failed to delete document", "error");
      }
    });
}

function updateStats(files) {
  const totalDocs = files.length;
  const totalDocsEl = document.querySelector(
    ".kb-stats .kb-stat-card:first-child p",
  );
  if (totalDocsEl) totalDocsEl.textContent = totalDocs;
}

function showToast(message, type = "success") {
  if (typeof showAlert === "function") {
    showAlert(message, type);
  } else {
    alert(message);
  }
}
