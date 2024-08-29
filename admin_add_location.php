<?php
session_start();
include 'db.php';

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<div class='alert alert-warning' role='alert'>Anda harus login sebagai admin untuk mengakses halaman ini.</div>";
    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
    exit();
}

// Menangani penambahan lokasi wisata
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Direktori untuk menyimpan gambar
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek jika file adalah gambar
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "<div class='alert alert-danger' role='alert'>File bukan gambar.</div>";
        $uploadOk = 0;
    }

    // Cek jika file sudah ada
    if (file_exists($target_file)) {
        echo "<div class='alert alert-danger' role='alert'>File sudah ada.</div>";
        $uploadOk = 0;
    }

    // Cek ukuran file
    if ($_FILES["image"]["size"] > 500000) {
        echo "<div class='alert alert-danger' role='alert'>File terlalu besar.</div>";
        $uploadOk = 0;
    }

    // Cek tipe file
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "<div class='alert alert-danger' role='alert'>Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.</div>";
        $uploadOk = 0;
    }

    // Cek jika $uploadOk di-set ke 0 oleh kesalahan
    if ($uploadOk == 0) {
        echo "<div class='alert alert-danger' role='alert'>File tidak di-upload.</div>";
    // Jika semuanya OK, coba untuk meng-upload file
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "<div class='alert alert-success' role='alert'>File ". htmlspecialchars(basename($_FILES["image"]["name"])) . " telah di-upload.</div>";

            // Simpan informasi lokasi wisata dan gambar ke database
            $stmt = $conn->prepare("INSERT INTO destinations (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $name, $description, $price, $target_file);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success' role='alert'>Lokasi wisata berhasil ditambahkan!</div>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
            }
            
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger' role='alert'>Terjadi kesalahan saat meng-upload file.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Lokasi Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                        <a class="nav-link" href="admin_add_location.php">Tambah Lokasi Wisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Tambah Lokasi Wisata</h1>
        <form action="admin_add_location.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lokasi:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi:</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga Tiket:</label>
                <input type="number" id="price" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar:</label>
                <input type="file" id="image" name="image" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Lokasi</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>
</html>
