<?php
// Clearance Status view
$title = "Clearance Status - " . APP_NAME;
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">Clearance Status</h4>
            </div>
            <div class="card-body">
                <div class="clearance-status mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Overall Status</h5>
                        <span class="badge bg-<?= $overallStatus === 'cleared' ? 'success' : 
                            ($overallStatus === 'pending' ? 'warning' : 'danger') ?> p-2">
                            <?= ucfirst($overallStatus ?? 'pending') ?>
                        </span>
                    </div>
                    
                    <?php if ($overallStatus === 'cleared'): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i> 
                            Congratulations! Your clearance process is complete.
                            <a href="/clearance/download" class="btn btn-sm btn-success ms-2">
                                Download Certificate
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="clearance-steps">
                    <?php foreach ($clearanceSteps as $step): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title"><?= htmlspecialchars($step['name']) ?></h5>
                                    <span class="badge bg-<?= $step['status'] === 'cleared' ? 'success' : 
                                        ($step['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($step['status']) ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted"><?= htmlspecialchars($step['description']) ?></p>
                                
                                <?php if ($step['status'] === 'pending' && !empty($step['action'])): ?>
                                    <a href="<?= $step['action']['url'] ?>" class="btn btn-primary btn-sm">
                                        <?= htmlspecialchars($step['action']['text']) ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($step['comment'])): ?>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <small><strong>Note:</strong> <?= htmlspecialchars($step['comment']) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Requirements Checklist</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($requirements as $requirement): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-<?= $requirement['completed'] ? 'check text-success' : 'times text-danger' ?>"></i>
                                    <?= htmlspecialchars($requirement['name']) ?>
                                </div>
                            </div>
                            <?php if (!empty($requirement['description'])): ?>
                                <small class="text-muted d-block mt-1">
                                    <?= htmlspecialchars($requirement['description']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Need Help?</h5>
            </div>
            <div class="card-body">
                <p>If you have any questions or need assistance with your clearance process, please don't hesitate to contact us.</p>
                <div class="d-grid">
                    <a href="/support" class="btn btn-primary">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript for the clearance page here
});
</script>
<?php $this->pop('scripts') ?>
