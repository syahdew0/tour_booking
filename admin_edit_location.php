<?php
session_start();
include 'db.php';

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<div class='alert alert-warning' role='alert'>Anda harus login sebagai admin untuk mengakses halaman ini.</div>";
    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
    exit();
}

$id = intval($_GET['id']);

// Fungsi untuk mendapatkan ID video YouTube dari URL
function getYouTubeVideoId($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    return isset($params['v']) ? $params['v'] : null;
}

// Ambil data lokasi wisata
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $video_url = $_POST['video_url'];

    // Cek jika ada file gambar baru
    if ($_FILES['image']['name']) {
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
                $image = $target_file;
            } else {
                echo "<div class='alert alert-danger' role='alert'>Terjadi kesalahan saat meng-upload file.</div>";
                $image = $_POST['old_image'];
            }
        }
    } else {
        $image = $_POST['old_image'];
    }

    // Update data lokasi wisata
    $stmt = $conn->prepare("UPDATE destinations SET name = ?, description = ?, price = ?, image = ?, video_url = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $description, $price, $image, $video_url, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Lokasi wisata berhasil diperbarui!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Ambil data lokasi wisata untuk di-edit
$result = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$result->bind_param("i", $id);
$result->execute();
$data = $result->get_result()->fetch_assoc();
$result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lokasi Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Tour Booking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Daftar Lokasi Wisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Edit Lokasi Wisata</h1>
        <form action="admin_edit_location.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lokasi:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi:</label>
                <textarea id="description" name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga Tiket:</label>
                <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($data['price']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar:</label>
                <input type="file" id="image" name="image" class="form-control">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($data['image']); ?>">
                <img src="<?php echo htmlspecialchars($data['image']); ?>" alt="Gambar" width="100">
            </div>
            <div class="mb-3">
                <label for="video_url" class="form-label">Link Video YouTube:</label>
                <input type="text" id="video_url" name="video_url" class="form-control" value="<?php echo htmlspecialchars($data['video_url']); ?>">
                <?php if ($data['video_url']): ?>
                    <?php $videoId = getYouTubeVideoId($data['video_url']); ?>
                    <?php if ($videoId): ?>
                        <div class="mt-2">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-2">URL video tidak valid.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Lokasi</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
