<?php
// Payment view
$title = "Make Payment - " . APP_NAME;
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">Make Payment</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Current Balance:</strong> $<?= number_format($balance ?? 0, 2) ?>
                </div>

                <form action="/payment/process" method="POST" id="paymentForm">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount to Pay</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   min="1" step="0.01" required 
                                   value="<?= $balance ?? 0 ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                            <option value="">Select payment method</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mobile">Mobile Money</option>
                        </select>
                    </div>

                    <div id="cardDetails" class="d-none">
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="cardNumber" name="cardNumber" 
                                   pattern="[0-9]{16}" placeholder="1234 5678 9012 3456">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiryDate" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiryDate" name="expiryDate" 
                                       placeholder="MM/YY" pattern="[0-9]{2}/[0-9]{2}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                       pattern="[0-9]{3,4}" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <div id="bankDetails" class="d-none">
                        <div class="alert alert-info">
                            <h5>Bank Transfer Details</h5>
                            <p class="mb-1"><strong>Bank:</strong> Example Bank</p>
                            <p class="mb-1"><strong>Account Name:</strong> University Clearance</p>
                            <p class="mb-1"><strong>Account Number:</strong> 1234567890</p>
                            <p class="mb-0"><strong>Reference:</strong> <?= $user['studentId'] ?? '' ?></p>
                        </div>
                    </div>

                    <div id="mobileDetails" class="d-none">
                        <div class="mb-3">
                            <label for="mobileNumber" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobileNumber" name="mobileNumber" 
                                   placeholder="Enter your mobile number">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                        <a href="/dashboard" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4 class="mb-0">Payment History</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= $payment['date'] ?></td>
                                        <td>$<?= number_format($payment['amount'], 2) ?></td>
                                        <td><?= ucfirst($payment['method']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $payment['status'] === 'completed' ? 'success' : 
                                                ($payment['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $payment['reference'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No payment history available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('paymentMethod');
    const cardDetails = document.getElementById('cardDetails');
    const bankDetails = document.getElementById('bankDetails');
    const mobileDetails = document.getElementById('mobileDetails');

    paymentMethod.addEventListener('change', function() {
        cardDetails.classList.add('d-none');
        bankDetails.classList.add('d-none');
        mobileDetails.classList.add('d-none');

        switch(this.value) {
            case 'card':
                cardDetails.classList.remove('d-none');
                break;
            case 'bank':
                bankDetails.classList.remove('d-none');
                break;
            case 'mobile':
                mobileDetails.classList.remove('d-none');
                break;
        }
    });
});
</script>
<?php $this->pop('scripts') ?>
