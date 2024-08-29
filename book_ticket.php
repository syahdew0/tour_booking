<?php
session_start();
include 'db.php';

// Ambil daftar lokasi wisata untuk dropdown
$destinations = $conn->query("SELECT id, name, price FROM destinations");

// Menangani pemesanan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    $name = $_POST['name'];
    $identity_number = $_POST['identity_number'];
    $phone_number = $_POST['phone_number'];
    $destination_id = $_POST['destination_id'];
    $visit_date = $_POST['visit_date']; // Format DD-MM-YY misalnya
    $adult_count = $_POST['adult_count'];
    $child_count = $_POST['child_count'];
    $user_id = $_SESSION['user_id']; // Mengambil ID pengguna dari sesi

    echo "Tanggal yang diterima: " . $visit_date . "<br>";
    // Validasi dan konversi format tanggal jika diperlukan
    // Misalnya, jika input format DD-MM-YY:
    // $date_parts = explode('-', $visit_date);
    // if (count($date_parts) == 3) {
    //     $day = $date_parts[0];
    //     $month = $date_parts[1];
    //     $year = $date_parts[2];
    //     // Ubah ke format YYYY-MM-DD
    //     $visit_date = "$year-$month-$day";
    // }

    // Validasi format tanggal yang diubah
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date)) {
        die("Tanggal tidak valid. Pastikan format tanggal adalah YYYY-MM-DD.");
    }

    // Ambil harga tiket untuk lokasi wisata yang dipilih
    $stmt = $conn->prepare("SELECT price FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();

    // Hitung total bayar dengan potongan harga anak-anak 50%
    $adult_total = $price * $adult_count;
    $child_total = ($price * 0.5) * $child_count;
    $total_amount = $adult_total + $child_total;

    // Menyimpan pemesanan ke database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, identity_number, phone_number, destination_id, visit_date, adult_count, child_count, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiisiii", $user_id, $name, $identity_number, $phone_number, $destination_id, $visit_date, $adult_count, $child_count, $total_amount);

    // Debugging: Tampilkan data yang akan diinsert
    echo "Data yang akan diinsert: ";
    print_r(array($user_id, $name, $identity_number, $phone_number, $destination_id, $visit_date, $adult_count, $child_count, $total_amount));

    // Eksekusi query
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Tiket berhasil dipesan!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
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
        <h1 class="mb-4">Pesan Tiket Wisata</h1>
        <form action="book_ticket.php" method="post">
            <div class="row mb-3">
                <label for="name" class="col-sm-3 col-form-label">Nama Lengkap</label>
                <div class="col-sm-9">
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="identity_number" class="col-sm-3 col-form-label">Nomor Identitas</label>
                <div class="col-sm-9">
                    <input type="text" id="identity_number" name="identity_number" class="form-control" pattern="\d{16}"
                        maxlength="16" required title="Nomor identitas harus terdiri dari 16 digit angka">
                </div>
            </div>

            <div class="row mb-3">
                <label for="phone_number" class="col-sm-3 col-form-label">No. HP</label>
                <div class="col-sm-9">
                    <input type="number" id="phone_number" name="phone_number" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="destination_id" class="col-sm-3 col-form-label">Tempat Wisata</label>
                <div class="col-sm-9">
                    <select id="destination_id" name="destination_id" class="form-select" required
                        onchange="updatePrice()">
                        <option value="">Pilih Tempat Wisata</option>
                        <?php while ($row = $destinations->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label for="visit_date" class="col-sm-3 col-form-label">Tanggal Kunjungan</label>
                <div class="col-sm-9">
                    <input type="date" id="visit_date" name="visit_date" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="adult_count" class="col-sm-3 col-form-label">Pengunjung Dewasa</label>
                <div class="col-sm-9">
                    <input type="number" id="adult_count" name="adult_count" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="child_count" class="col-sm-3 col-form-label">Pengunjung Anak-anak</label>
                <div class="col-sm-9">
                    <input type="number" id="child_count" name="child_count" class="form-control" required>
                </div>
                <p style="font-size:11px">usia dibawah 12 tahun</p>
            </div>
            <div class="row mb-3">
                <label for="ticket_price" class="col-sm-3 col-form-label">Harga Tiket</label>
                <div class="col-sm-9">
                    <p id="ticket_price" data-price=""></p>
                </div>
            </div>
            <div class="row mb-3">
                <label for="total_amount" class="col-sm-3 col-form-label">Total Bayar</label>
                <div class="col-sm-9">
                    <p id="total_amount"></p>
                </div>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required>
                <label class="form-check-label" for="flexCheckDefault">
                    Saya dan/atau rombongan telah membaca, memahami, dan setuju berdasarkan syarat dan ketentuan yang
                    telah
                    ditetapkan
                </label>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-success" onclick="calculateTotal()">Hitung Total Bayar</button>
                <button type="submit" class="btn btn-primary">Pesan Tiket</button>
                <button type="button" class="btn btn-warning" onclick="window.location.href='index.php'">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }

        function calculateTotal() {
            const price = parseFloat(document.getElementById('ticket_price').dataset.price) || 0;
            const adultCount = parseInt(document.getElementById('adult_count').value) || 0;
            const childCount = parseInt(document.getElementById('child_count').value) || 0;

            // Hitung total dengan potongan harga anak-anak 50%
            const adultTotal = price * adultCount;
            const childTotal = (price * 0.5) * childCount;
            const total = adultTotal + childTotal;

            // Format total bayar
            document.getElementById('total_amount').textContent = formatRupiah(total);
        }

        function updatePrice() {
            const destinationId = document.getElementById('destination_id').value;
            fetch(`get_price.php?id=${destinationId}`)
                .then(response => response.json())
                .then(data => {
                    const price = data.price;
                    document.getElementById('ticket_price').dataset.price = price;
                    document.getElementById('ticket_price').textContent = formatRupiah(price);
                    calculateTotal(); // Optionally, calculate total when price is updated
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>