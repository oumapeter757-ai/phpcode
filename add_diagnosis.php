<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';


$patients_query = "SELECT id, name FROM patients";
$patients_result = $conn->query($patients_query);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];

    $query = "INSERT INTO diagnoses (patient_id, diagnosis, prescription) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $patient_id, $diagnosis, $prescription);
    $stmt->execute();

    $success_message = "Diagnosis and prescription added successfully!";
}

$diagnoses_query = "SELECT d.id, p.name AS patient_name, d.diagnosis, d.prescription, d.created_at AS date 
                    FROM diagnoses d 
                    JOIN patients p ON d.patient_id = p.id";
$diagnoses_result = $conn->query($diagnoses_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis & Prescriptions</title>
    <link rel="stylesheet" href="styles.css">

<body>
    <div class="dashboard-container">
        <h2>Diagnosis & Prescriptions</h2>


        <?php if (isset($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

        <div class="diagnosis-container">
            <h2>Diagnosis and Prescription</h2>

            <!-- Diagnosis Form -->
            <form method="POST" class="diagnosis-form">
                <label for="patient_id">Select Patient:</label>
                <select name="patient_id" id="patient_id" required>
                    <option value="">-- Select Patient --</option>
                    <?php while ($patient = $patients_result->fetch_assoc()) { ?>
                        <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['name']); ?></option>
                    <?php } ?>
                </select>

                <label for="diagnosis">Diagnosis:</label>
                <textarea name="diagnosis" id="diagnosis" rows="5" required></textarea>

                <label for="prescription">Prescription:</label>
                <textarea name="prescription" id="prescription" rows="5" required></textarea>

                <button type="submit">Submit</button>
            </form>

            <!-- Diagnosis and Prescription Table -->
            <h3>Previous Diagnoses and Prescriptions</h3>
            <table class="diagnosis-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($diagnosis = $diagnoses_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($diagnosis['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($diagnosis['diagnosis']); ?></td>
                            <td><?php echo htmlspecialchars($diagnosis['prescription']); ?></td>
                            <td><?php echo htmlspecialchars($diagnosis['date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>