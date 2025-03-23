<?php



include 'db_connection.php';


$total_revenue_query = "SELECT SUM(amount) AS total_revenue FROM billing";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];


$total_bills_query = "SELECT COUNT(*) AS total_bills FROM billing";
$total_bills_result = $conn->query($total_bills_query);
$total_bills = $total_bills_result->fetch_assoc()['total_bills'];


$total_patients_query = "SELECT COUNT(*) AS total_patients FROM patients";
$total_patients_result = $conn->query($total_patients_query);
$total_patients = $total_patients_result->fetch_assoc()['total_patients'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="report.css">
</head>

<body>
    <div class="page-container">
        <h1>Reports</h1>

      
        <div class="reports-summary">
            <div class="report-card">
                <h2>Total Revenue</h2>
                <p>$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
            <div class="report-card">
                <h2>Total Bills</h2>
                <p><?php echo $total_bills; ?></p>
            </div>
            <div class="report-card">
                <h2>Total Patients</h2>
                <p><?php echo $total_patients; ?></p>
            </div>
        </div>

        
        <div class="return-dashboard">
            <a href="admin_dashboard.php" class="dashboard-link">Return to Admin Dashboard</a>
        </div>
    </div>
</body>

</html>