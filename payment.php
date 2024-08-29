<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    echo "<div class='alert alert-warning' role='alert'>Anda harus login dan memilih pemesanan untuk melanjutkan pembayaran.</div>";
    exit();
}

$bookingId = intval($_GET['booking_id']);

// Ambil detail pemesanan
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo "<div class='alert alert-danger' role='alert'>Pemesanannya tidak ditemukan.</div>";
    exit();
}

$totalAmount = number_format($booking['total_amount'], 2, ',', '.');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Tour Booking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Navigation items here -->
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Pembayaran</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detail Pemesanan</h5>
                <p class="card-text">ID Pemesanan: <?php echo htmlspecialchars($bookingId); ?></p>
                <p class="card-text">Total Bayar: Rp <?php echo $totalAmount; ?></p>
                
                <!-- Form pembayaran -->
                <form action="process_payment.php" method="post">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran:</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            <option value="" disabled selected>Pilih Metode Pembayaran</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <!-- Tambahkan metode pembayaran lain sesuai kebutuhan -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
