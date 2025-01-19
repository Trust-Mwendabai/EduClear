<?php
include 'database.php'; // Include database connection

// Function to get user dashboard data
function getUserDashboardData($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Payments WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC); // Return payment history
}

// Function to get admin dashboard data
function getAdminDashboardData() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Payments");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC); // Return all payment records
}
?>
