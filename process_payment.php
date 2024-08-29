<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id']) || !isset($_POST['booking_id']) || !isset($_POST['payment_method'])) {
        echo "<div class='alert alert-warning' role='alert'>Data tidak lengkap.</div>";
        exit();
    }

    $bookingId = intval($_POST['booking_id']);
    $paymentMethod = $_POST['payment_method'];

    // Proses pembayaran berdasarkan metode
    switch ($paymentMethod) {
        case 'credit_card':
            // Proses pembayaran dengan kartu kredit (misalnya integrasi dengan gateway pembayaran)
            echo "<div class='alert alert-success' role='alert'>Pembayaran dengan kartu kredit sedang diproses.</div>";
            break;

        case 'bank_transfer':
            // Proses pembayaran dengan transfer bank
            echo "<div class='alert alert-success' role='alert'>Pembayaran dengan transfer bank sedang diproses.</div>";
            break;

        default:
            echo "<div class='alert alert-danger' role='alert'>Metode pembayaran tidak dikenali.</div>";
            exit();
    }

    // Tandai pemesanan sebagai telah dibayar
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Pembayaran berhasil!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>
