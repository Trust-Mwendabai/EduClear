<?php
include 'database.php'; // Include database connection

// Function to process payment
function processPayment($userId, $amount) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Payments (user_id, amount, payment_date, status) VALUES (?, ?, NOW(), 'pending')");
    $stmt->bind_param("id", $userId, $amount);
    if ($stmt->execute()) {
        // Here you would integrate with the payment gateway
        // For now, we'll simulate a successful payment
        $stmt = $conn->prepare("UPDATE Payments SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $conn->insert_id);
        $stmt->execute();
        return true; // Payment processed successfully
    }
    return false; // Payment processing failed
}
?>
