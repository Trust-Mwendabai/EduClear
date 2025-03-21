<?php
// Admin Dashboard view
$title = "Admin Dashboard - " . APP_NAME;
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="<?= $admin['avatar'] ?? '/assets/images/default-avatar.png' ?>" 
                     class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="card-title"><?= htmlspecialchars($admin['name']) ?></h5>
                <p class="text-muted">Administrator</p>
            </div>
        </div>

        <div class="list-group mb-4">
            <a href="/admin" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="/admin/students" class="list-group-item list-group-item-action">Students</a>
            <a href="/admin/clearances" class="list-group-item list-group-item-action">Clearances</a>
            <a href="/admin/payments" class="list-group-item list-group-item-action">Payments</a>
            <a href="/admin/reports" class="list-group-item list-group-item-action">Reports</a>
            <a href="/admin/settings" class="list-group-item list-group-item-action">Settings</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <p class="display-4"><?= $totalStudents ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Cleared Students</h5>
                        <p class="display-4"><?= $clearedStudents ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <p class="display-4"><?= $pendingClearances ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="h4">$<?= number_format($totalRevenue ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Clearance Requests</h5>
                <a href="/admin/clearances" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentClearances)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentClearances as $clearance): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($clearance['studentId']) ?></td>
                                        <td><?= htmlspecialchars($clearance['studentName']) ?></td>
                                        <td><?= $clearance['date'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $clearance['status'] === 'approved' ? 'success' : 
                                                ($clearance['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($clearance['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/admin/clearance/<?= $clearance['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No recent clearance requests</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Payments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentPayments)): ?>
                            <div class="list-group">
                                <?php foreach ($recentPayments as $payment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($payment['studentName']) ?></h6>
                                            <small class="text-muted"><?= $payment['date'] ?></small>
                                        </div>
                                        <p class="mb-1">Amount: $<?= number_format($payment['amount'], 2) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent payments</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">System Statistics</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statisticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statisticsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels ?? []) ?>,
            datasets: [{
                label: 'Clearance Requests',
                data: <?= json_encode($chartData ?? []) ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php $this->pop('scripts') ?>
