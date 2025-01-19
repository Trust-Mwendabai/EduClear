<?php
include 'database.php'; // Include database connection

// Function to generate clearance form
function generateClearanceForm($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Payments WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate PDF logic here (using a library like TCPDF or FPDF)
        // For now, we'll simulate the generation
        return "Clearance form generated successfully for user ID: " . $userId;
    } else {
        return "No completed payments found for user ID: " . $userId;
    }
}

// Example usage
echo generateClearanceForm(1); // Replace with actual user ID
?>
