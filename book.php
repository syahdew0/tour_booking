<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning' role='alert'>Anda harus login untuk memesan.</div>";
    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
    exit();
}

if (isset($_GET['id'])) {
    $destination_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $booking_date = date('Y-m-d');

    $sql = "INSERT INTO bookings (user_id, destination_id, booking_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $destination_id, $booking_date);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Pemesanan berhasil!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<a href="index.php" class="btn btn-secondary mt-3">Kembali ke daftar destinasi</a>

<?php
$conn->close();
?>
