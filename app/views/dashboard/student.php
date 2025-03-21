<?php
// Student Dashboard view
$title = "Student Dashboard - " . APP_NAME;
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="<?= $user['avatar'] ?? '/assets/images/default-avatar.png' ?>" 
                     class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="card-title"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($user['studentId']) ?></p>
            </div>
        </div>

        <div class="list-group mb-4">
            <a href="/dashboard" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="/clearance" class="list-group-item list-group-item-action">Clearance Status</a>
            <a href="/payment" class="list-group-item list-group-item-action">Payments</a>
            <a href="/notifications" class="list-group-item list-group-item-action">Notifications</a>
            <a href="/settings" class="list-group-item list-group-item-action">Settings</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Clearance Status</h5>
                        <p class="display-4"><?= $clearanceStatus ?? 'Pending' ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Outstanding Balance</h5>
                        <p class="display-4">$<?= number_format($balance ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Notifications</h5>
                        <p class="display-4"><?= $notificationCount ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($activities)): ?>
                    <div class="list-group">
                        <?php foreach ($activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                    <small class="text-muted"><?= $activity['date'] ?></small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="/payment" class="btn btn-primary d-block mb-2">Make Payment</a>
                    </div>
                    <div class="col-md-4">
                        <a href="/clearance" class="btn btn-success d-block mb-2">View Clearance</a>
                    </div>
                    <div class="col-md-4">
                        <a href="/support" class="btn btn-info d-block mb-2">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
