// Student Dashboard API
class StudentDashboardAPI {
    static async fetchPaymentSummary() {
        try {
            const response = await fetch('/api/student/payment-summary.php', {
                headers: {
                    'X-CSRF-Token': window.csrfToken
                }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching payment summary:', error);
            return null;
        }
    }

    static async fetchClearanceRequirements() {
        try {
            const response = await fetch('/api/student/clearance-requirements.php', {
                headers: {
                    'X-CSRF-Token': window.csrfToken
                }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching clearance requirements:', error);
            return null;
        }
    }

    static async fetchActivityLog() {
        try {
            const response = await fetch('/api/student/activity-log.php', {
                headers: {
                    'X-CSRF-Token': window.csrfToken
                }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching activity log:', error);
            return null;
        }
    }
}

// Student Dashboard UI
class StudentDashboardUI {
    static updatePaymentSummary(data) {
        if (!data) return;

        document.getElementById('totalDue').textContent = `$${data.totalDue.toLocaleString()}`;
        document.getElementById('paidAmount').textContent = `$${data.paidAmount.toLocaleString()}`;
        document.getElementById('balance').textContent = `$${data.balance.toLocaleString()}`;
    }

    static updateClearanceTimeline(requirements) {
        if (!requirements) return;

        const timeline = document.getElementById('clearanceTimeline');
        timeline.innerHTML = requirements.map(req => `
            <div class="timeline-item">
                <div class="d-flex justify-content-between">
                    <h6>${req.requirement}</h6>
                    <span class="badge badge-${req.status === 'completed' ? 'success' : 
                                           req.status === 'pending' ? 'warning' : 'danger'}">
                        ${req.status}
                    </span>
                </div>
                <p class="text-muted mb-0">${req.description}</p>
                ${req.status === 'pending' ? `
                    <button class="btn btn-sm btn-outline-primary mt-2" 
                            onclick="handleRequirement('${req.id}')">
                        Complete Requirement
                    </button>
                ` : ''}
            </div>
        `).join('');
    }

    static updateActivityLog(activities) {
        if (!activities) return;

        const activityLog = document.getElementById('activityLog');
        activityLog.innerHTML = activities.map(activity => `
            <tr>
                <td>${new Date(activity.date).toLocaleDateString()}</td>
                <td>${activity.activity}</td>
                <td>
                    <span class="badge badge-${activity.status === 'completed' ? 'success' : 
                                              activity.status === 'pending' ? 'warning' : 'danger'}">
                        ${activity.status}
                    </span>
                </td>
                <td>${activity.details}</td>
            </tr>
        `).join('');
    }

    static showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> ${message}
        `;
        document.querySelector('.container').prepend(alertDiv);
    }
}

// Event Handlers
async function handleRequirement(requirementId) {
    try {
        const response = await fetch('/api/student/complete-requirement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken
            },
            body: JSON.stringify({ requirementId })
        });

        if (!response.ok) throw new Error('Failed to complete requirement');
        
        // Refresh the requirements list
        const requirements = await StudentDashboardAPI.fetchClearanceRequirements();
        StudentDashboardUI.updateClearanceTimeline(requirements);
    } catch (error) {
        console.error('Error completing requirement:', error);
        StudentDashboardUI.showError('Failed to complete requirement. Please try again.');
    }
}

// Initialize Dashboard
async function initDashboard() {
    try {
        // Show loading state
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.style.opacity = '0.6';
        });

        // Fetch all data concurrently
        const [paymentSummary, requirements, activities] = await Promise.all([
            StudentDashboardAPI.fetchPaymentSummary(),
            StudentDashboardAPI.fetchClearanceRequirements(),
            StudentDashboardAPI.fetchActivityLog()
        ]);

        // Update UI
        StudentDashboardUI.updatePaymentSummary(paymentSummary);
        StudentDashboardUI.updateClearanceTimeline(requirements);
        StudentDashboardUI.updateActivityLog(activities);
    } catch (error) {
        console.error('Dashboard initialization error:', error);
        StudentDashboardUI.showError('Error loading dashboard data. Please refresh the page.');
    } finally {
        // Remove loading state
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.style.opacity = '1';
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initDashboard);

// Refresh data periodically
setInterval(initDashboard, 5 * 60 * 1000); // Refresh every 5 minutes
