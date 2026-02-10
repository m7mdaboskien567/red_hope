/**
 * RedHope Hospital Admin Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    initBloodRequestForm();
    initCancelRequestButtons();
    initAdminProfileForm();
    initPasswordForm();
    initInventoryFilter();
});

/**
 * Blood Request Form Handler
 */
function initBloodRequestForm() {
    const form = document.getElementById('bloodRequestForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Submitting...';
        btn.disabled = true;

        const formData = {
            hospital_id: form.hospital_id.value,
            blood_type_required: form.blood_type_required.value,
            units_requested: parseInt(form.units_requested.value),
            urgency_level: form.urgency_level.value,
            patient_identifier: form.patient_identifier.value
        };

        try {
            const response = await fetch('/redhope/apis/create_blood_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = '/redhope/dashboard/hospital_admin/?tab=requests&success=1';
            } else {
                showAlert(result.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

/**
 * Cancel Request Buttons
 */
async function confirmCancelRequest(requestId) {
    if (!confirm('Are you sure you want to cancel this blood request?')) {
        return;
    }

    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
    btn.disabled = true;

    try {
        const response = await fetch('/redhope/apis/cancel_blood_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: requestId })
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            showAlert(result.message, 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

function initCancelRequestButtons() {
    // Handled via onclick in HTML
}

/**
 * Admin Profile Form Handler
 */
function initAdminProfileForm() {
    const form = document.getElementById('adminProfileForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Saving...';
        btn.disabled = true;

        const formData = {
            first_name: form.first_name.value,
            last_name: form.last_name.value,
            email: form.email.value,
            phone: form.phone.value
        };

        try {
            const response = await fetch('/redhope/apis/update_hospital_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

/**
 * Password Form Handler (reuse from profile.js logic)
 */
function initPasswordForm() {
    const form = document.getElementById('passwordForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        if (form.new_password.value !== form.confirm_password.value) {
            showAlert('New passwords do not match.', 'error');
            return;
        }

        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Changing...';
        btn.disabled = true;

        const formData = {
            current_password: form.current_password.value,
            new_password: form.new_password.value,
            confirm_password: form.confirm_password.value
        };

        try {
            const response = await fetch('/redhope/apis/change_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                form.reset();
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

/**
 * Inventory Filter
 */
function initInventoryFilter() {
    window.filterInventory = function(bloodType) {
        const rows = document.querySelectorAll('#inventoryTableBody tr');
        rows.forEach(row => {
            if (bloodType === 'all' || row.dataset.bloodType === bloodType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    };
}

// CSS for spin animation (if not already added)
if (!document.querySelector('#hospital-spin-style')) {
    const style = document.createElement('style');
    style.id = 'hospital-spin-style';
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
}
