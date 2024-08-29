<?php
session_start();
include 'db.php';

$sql = "SELECT * FROM destinations";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinasi Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Tour Booking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_add_location.php">Tambah Lokasi Wisata</a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="book_ticket.php">Pesan Tiket</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php">Riwayat Pemesanan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Destinasi Wisata Sumutera Utara</h1>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='col-md-4 mb-4'>";
                    echo "<div class='card'>";
                    
                    // Gambar
                    echo "<img src='" . htmlspecialchars($row["image"]) . "' class='card-img-top' alt='" . htmlspecialchars($row["name"]) . "'>";
                    
                    echo "<div class='card-body'>";
                    
                   
                    // Video
                    if (!empty($row["video_url"])) {
                        $videoId = getYouTubeVideoId($row["video_url"]); // Ambil ID video YouTube dari URL
                        if ($videoId) {
                            echo "<div class='ratio ratio-16x9 mb-3'>";
                            echo "<iframe src='https://www.youtube.com/embed/" . htmlspecialchars($videoId) . "' title='YouTube video player' allowfullscreen></iframe>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning mb-3'>URL video tidak valid.</div>";
                        }
                    }
                    
                     // Judul dan Deskripsi
                     echo "<h5 class='card-title'>" . htmlspecialchars($row["name"]) . "</h5>";
                     echo "<p class='card-text'>" . htmlspecialchars($row["description"]) . "</p>";
                     echo "<p class='card-text'>Harga: Rp " . number_format($row["price"], 2, ',', '.') . "</p>";
                     
                    // Tombol Pemesanan (Opsional)
                    // if (isset($_SESSION['user_id'])) {
                    //     echo "<a href='book.php?id=" . $row["id"] . "' class='btn btn-primary'>Pesan Sekarang</a>";
                    // }
                    
                    echo "</div>"; // End of card-body
                    echo "</div>"; // End of card
                    echo "</div>"; // End of col-md-4
                }
            } else {
                echo "<p>Tidak ada destinasi.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
</body>
<!-- Footer -->
<footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © 2024 Tour Booking: 
            <a class="text-dark" href="https:/wa.me/6282360599486 " target="_blank">By Reza Syahdewo</a> 
        </div>
    </footer>

</html>

<?php
$conn->close();

// Fungsi untuk mendapatkan ID video YouTube dari URL
function getYouTubeVideoId($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    return isset($params['v']) ? $params['v'] : null;
}
?>
