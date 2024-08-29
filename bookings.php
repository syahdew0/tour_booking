<?php
session_start();
include 'db.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning' role='alert'>Anda harus login untuk melihat riwayat pemesanan.</div>";
    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk mengambil riwayat pemesanan
$sql = "SELECT b.id, d.name AS destination_name, d.price, b.visit_date, b.name AS user_name, b.identity_number, b.phone_number, b.adult_count, b.child_count, b.total_amount, b.payment_status
        FROM bookings b 
        JOIN destinations d ON b.destination_id = d.id 
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card {
            margin-bottom: 1rem;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Tour Booking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Riwayat Pemesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Riwayat Pemesanan</h1>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<table class='table'>";
                echo "<tbody>";
                echo "<tr><td>Nama Pemesan</td><td>: " . htmlspecialchars($row["user_name"]) . "</td></tr>";
                echo "<tr><td>Nomor Identitas</td><td>: " . htmlspecialchars($row["identity_number"]) . "</td></tr>";
                echo "<tr><td>No. HP</td><td>: " . htmlspecialchars($row["phone_number"]) . "</td></tr>";
                echo "<tr><td>Tempat Wisata</td><td>: " . htmlspecialchars($row["destination_name"]) . "</td></tr>";
                echo "<tr><td>Pengunjung Dewasa</td><td>: " . htmlspecialchars($row["adult_count"]) . " orang</td></tr>";
                echo "<tr><td>Pengunjung Anak-anak</td><td>: " . htmlspecialchars($row["child_count"]) . " orang</td></tr>";
                echo "<tr><td>Harga Tiket</td><td>: Rp " . formatRupiah($row["price"]) . "</td></tr>";
                echo "<tr><td>Total Bayar</td><td>: Rp " . formatRupiah($row["total_amount"]) . "</td></tr>";
                echo "</tbody>";
                echo "</table>";
                
                // Tambahkan tombol pembayaran jika status pemesanan adalah 'unpaid'
                if ($row["payment_status"] == 'unpaid') {
                    echo "<a href='payment.php?booking_id=" . htmlspecialchars($row["id"]) . "' class='btn btn-primary'>Bayar Sekarang</a>";
                } else {
                    echo "<p class='text-success'>Pembayaran sudah selesai.</p>";
                }

                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Anda belum melakukan pemesanan.</p>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();

// Mendefinisikan fungsi formatRupiah
function formatRupiah($amount) {
    return number_format($amount, 0, ',', '.');
}
?>
