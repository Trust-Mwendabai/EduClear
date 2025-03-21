// Dashboard API integration
class DashboardAPI {
    static async fetchStats() {
        try {
            const response = await fetch('/api/dashboard.php?action=getDashboardStats');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (error) {
            console.error('Error fetching dashboard stats:', error);
            return null;
        }
    }

    static async fetchPaymentHistory() {
        try {
            const response = await fetch('/api/dashboard.php?action=getPaymentHistory');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            return data.success ? data.data : [];
        } catch (error) {
            console.error('Error fetching payment history:', error);
            return [];
        }
    }

    static async fetchStudentStatus() {
        try {
            const response = await fetch('/api/dashboard.php?action=getStudentStatus');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            return data.success ? data.data : [];
        } catch (error) {
            console.error('Error fetching student status:', error);
            return [];
        }
    }
}

// Dashboard UI updates
class DashboardUI {
    static updateStats(stats) {
        if (!stats) return;
        
        document.getElementById('totalPayments').textContent = `$${stats.totalPayments.toLocaleString()}`;
        document.getElementById('clearedStudents').textContent = stats.clearedStudents;
        document.getElementById('pendingPayments').textContent = stats.pendingPayments;
        document.getElementById('totalStudents').textContent = stats.totalStudents;
    }

    static updatePaymentRecords(payments) {
        const tbody = document.getElementById('paymentRecords');
        tbody.innerHTML = '';

        payments.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${payment.student_id}</td>
                <td>${payment.full_name}</td>
                <td>$${payment.amount}</td>
                <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                <td><span class="badge badge-${payment.status === 'cleared' ? 'success' : 'warning'}">${payment.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewPayment('${payment.id}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="editPayment('${payment.id}')">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    static updateCharts(paymentTrends, studentStatus) {
        // Payment Trends Chart
        const paymentTrendsCtx = document.getElementById('paymentTrends').getContext('2d');
        new Chart(paymentTrendsCtx, {
            type: 'line',
            data: {
                labels: paymentTrends.map(p => p.month),
                datasets: [{
                    label: 'Monthly Payments ($)',
                    data: paymentTrends.map(p => p.amount),
                    borderColor: '#007bff',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Clearance Status Chart
        const clearanceStatusCtx = document.getElementById('clearanceStatus').getContext('2d');
        new Chart(clearanceStatusCtx, {
            type: 'doughnut',
            data: {
                labels: studentStatus.map(s => s.clearance_status),
                datasets: [{
                    data: studentStatus.map(s => s.count),
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

// Event handlers
function viewPayment(id) {
    window.location.href = `/payment_details.php?id=${id}`;
}

function editPayment(id) {
    window.location.href = `/edit_payment.php?id=${id}`;
}

// Initialize dashboard
async function initDashboard() {
    // Show loading states
    document.querySelectorAll('.dashboard-card').forEach(card => {
        card.style.opacity = '0.6';
    });

    try {
        // Fetch all data concurrently
        const [stats, payments, studentStatus] = await Promise.all([
            DashboardAPI.fetchStats(),
            DashboardAPI.fetchPaymentHistory(),
            DashboardAPI.fetchStudentStatus()
        ]);

        // Update UI with real data
        DashboardUI.updateStats(stats);
        DashboardUI.updatePaymentRecords(payments);
        DashboardUI.updateCharts(payments, studentStatus);
    } catch (error) {
        console.error('Dashboard initialization error:', error);
        // Show error message to user
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger mt-3';
        alertDiv.textContent = 'Error loading dashboard data. Please refresh the page.';
        document.querySelector('.container').prepend(alertDiv);
    } finally {
        // Remove loading states
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.style.opacity = '1';
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initDashboard);

// Refresh data periodically
setInterval(initDashboard, 5 * 60 * 1000); // Refresh every 5 minutes
