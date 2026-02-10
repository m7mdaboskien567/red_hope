/**
 * Admin Dashboard Management Functions
 */

// Hospital Management
async function verifyHospital(hospitalId, btn) {
    if (!confirm('Are you sure you want to verify this hospital?')) return;
    
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
    btn.disabled = true;

    try {
        const response = await fetch('/redhope/apis/admin/manage_hospitals.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify', hospital_id: hospitalId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Hospital verified successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(result.message, 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    } catch (error) {
        showAlert('An error occurred', 'error');
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

async function deleteHospital(hospitalId) {
    if (!confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) return;

    try {
        const response = await fetch('/redhope/apis/admin/manage_hospitals.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', hospital_id: hospitalId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Hospital deleted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('An error occurred', 'error');
    }
}

// User Management
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

    try {
        const response = await fetch('/redhope/apis/admin/manage_users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', user_id: userId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('User deleted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('An error occurred', 'error');
    }
}

// Center Management
async function deleteCenter(centerId) {
    if (!confirm('Are you sure you want to delete this blood center?')) return;

    try {
        const response = await fetch('/redhope/apis/admin/manage_centers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', center_id: centerId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Blood center deleted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('An error occurred', 'error');
    }
}

// Modals
function openHospitalForm(hospitalData = null) {
    window.location.href = '/redhope/admin/hospital_form.php' + (hospitalData ? '?id=' + hospitalData.hospital_id : '');
}

function openUserForm() {
    showAlert('User form not implemented in this demo', 'info');
}

function openCenterForm() {
    showAlert('Center form not implemented in this demo', 'info');
}
