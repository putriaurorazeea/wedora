
<?php
require_once "koneksi.php";

// ========================================
// 1. PENGATURAN WHATSAPP ADMIN
// ========================================

$nomorAdmin = "62895329229304";

// ========================================
// 2. FUNGSI MENAMPILKAN PESAN ERROR
// ========================================

function tampilkanError($errors)
{
    echo "<!DOCTYPE html>";
    echo "<html lang='id'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Booking WEDORA</title>";
    echo "<script src='https://cdn.tailwindcss.com'></script>";
    echo "</head>";

    echo "<body class='bg-[#FDFBF7] min-h-screen flex items-center justify-center p-6'>";
    echo "<div class='bg-white max-w-lg w-full p-8 rounded-2xl shadow-lg'>";
    echo "<h1 class='text-3xl font-bold text-[#8B5A5A] mb-5'>Booking Belum Berhasil</h1>";
    echo "<ul class='list-disc pl-5 text-gray-700 space-y-2'>";

    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</li>";
    }

    echo "</ul>";
    echo "<a href='index.php#booking' class='block text-center mt-7 bg-[#C08081] text-white py-3 rounded-full'>";
    echo "Kembali ke Form Booking";
    echo "</a>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

// ========================================
// 3. MEMASTIKAN REQUEST DARI FORM
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// ========================================
// 4. MENGAMBIL DATA FORM
// ========================================

$nama = trim($_POST["nama"] ?? "");
$nomor_wa = trim($_POST["nomor_wa"] ?? "");
$tanggal_acara = trim($_POST["tanggal_acara"] ?? "");
$paket = trim($_POST["paket"] ?? "");
$lokasi = trim($_POST["lokasi"] ?? "");
$catatan = trim($_POST["catatan"] ?? "");

// Mengambil ID vendor yang dipilih
$vendorInput = $_POST["vendor_ids"] ?? [];

if (!is_array($vendorInput)) {
    $vendorInput = explode(",", $vendorInput);
}

$vendorIds = [];

foreach ($vendorInput as $id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0) {
        $vendorIds[] = $id;
    }
}

// Menghindari ID vendor yang sama
$vendorIds = array_values(array_unique($vendorIds));

// ========================================
// 5. VALIDASI DATA
// ========================================

$errors = [];

if ($nama === "" || strlen($nama) > 100) {
    $errors[] = "Nama wajib diisi dan maksimal 100 karakter.";
}

if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $nomor_wa)) {
    $errors[] = "Nomor WhatsApp tidak valid.";
}

$nomorBersih = preg_replace('/[^0-9]/', '', $nomor_wa);

if (strlen($nomorBersih) < 8 || strlen($nomorBersih) > 15) {
    $errors[] = "Panjang nomor WhatsApp tidak valid.";
}

if ($tanggal_acara === "") {
    $errors[] = "Tanggal acara wajib diisi.";
} else {
    $tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal_acara);

    if (!$tanggal || $tanggal->format('Y-m-d') !== $tanggal_acara) {
        $errors[] = "Format tanggal acara tidak valid.";
    } elseif ($tanggal_acara < date('Y-m-d')) {
        $errors[] = "Tanggal acara tidak boleh sebelum hari ini.";
    }
}

$paketValid = ["Silver", "Gold", "Platinum"];

if (!in_array($paket, $paketValid, true)) {
    $errors[] = "Pilihan paket tidak valid.";
}

if ($lokasi === "" || strlen($lokasi) > 255) {
    $errors[] = "Lokasi wajib diisi dan maksimal 255 karakter.";
}

if (strlen($catatan) > 3000) {
    $errors[] = "Catatan maksimal 3000 karakter.";
}

if (!empty($errors)) {
    tampilkanError($errors);
}

// ========================================
// 6. MEMERIKSA VENDOR YANG DIPILIH
// ========================================

$vendorTerpilih = [];

try {

    if (!empty($vendorIds)) {

        $placeholders = implode(",", array_fill(0, count($vendorIds), "?"));

        $sqlVendor = "
            SELECT id, nama_vendor, harga_mulai
            FROM vendors
            WHERE id IN ($placeholders)
            AND status = 'aktif'
        ";

        $stmtVendor = $conn->prepare($sqlVendor);

        if (!$stmtVendor) {
            throw new Exception("Gagal memeriksa data vendor.");
        }

        $types = str_repeat("i", count($vendorIds));
        $params = [$types];

        foreach ($vendorIds as $key => $id) {
            $params[] = &$vendorIds[$key];
        }

        call_user_func_array(
            [$stmtVendor, "bind_param"],
            $params
        );

        $stmtVendor->execute();

        $resultVendor = $stmtVendor->get_result();

        $vendorTerpilih = $resultVendor->fetch_all(MYSQLI_ASSOC);

        $stmtVendor->close();

        if (count($vendorTerpilih) !== count($vendorIds)) {
            throw new Exception(
                "Ada vendor yang sudah tidak tersedia. Silakan pilih ulang vendor."
            );
        }
    }

    // ========================================
    // 7. MENYIMPAN BOOKING DAN VENDOR
    // ========================================

    $conn->begin_transaction();

    $sqlBooking = "
        INSERT INTO bookings
        (nama, nomor_wa, tanggal_acara, paket, lokasi, catatan)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmtBooking = $conn->prepare($sqlBooking);

    if (!$stmtBooking) {
        throw new Exception("Gagal menyiapkan data booking.");
    }

    $stmtBooking->bind_param(
        "ssssss",
        $nama,
        $nomor_wa,
        $tanggal_acara,
        $paket,
        $lokasi,
        $catatan
    );

    if (!$stmtBooking->execute()) {
        throw new Exception("Data booking gagal disimpan.");
    }

    // Mengambil ID booking yang baru dibuat
    $bookingId = $conn->insert_id;

    $stmtBooking->close();

    // Menyimpan setiap vendor yang dipilih
    $sqlBookingVendor = "
        INSERT INTO booking_vendors
        (booking_id, vendor_id, harga_saat_booking)
        VALUES (?, ?, ?)
    ";

    $stmtBookingVendor = $conn->prepare($sqlBookingVendor);

    if (!$stmtBookingVendor) {
        throw new Exception("Gagal menyiapkan data vendor booking.");
    }

    foreach ($vendorTerpilih as $vendor) {

        $vendorId = (int) $vendor["id"];
        $harga = (float) $vendor["harga_mulai"];

        $stmtBookingVendor->bind_param(
            "iid",
            $bookingId,
            $vendorId,
            $harga
        );

        if (!$stmtBookingVendor->execute()) {
            throw new Exception("Data vendor booking gagal disimpan.");
        }
    }

    $stmtBookingVendor->close();

    // Semua data berhasil disimpan
    $conn->commit();

    // ========================================
    // 8. MEMBUAT PESAN WHATSAPP
    // ========================================

    $pesan = "Halo Admin WO Rumah Kolaborasi!";
    $pesan .= "\n\nSaya ingin melakukan booking paket pernikahan.";
    $pesan .= "\n\n*DATA BOOKING WEDORA*";
    $pesan .= "\nID Booking: " . $bookingId;
    $pesan .= "\nNama: " . $nama;
    $pesan .= "\nNomor WhatsApp: " . $nomor_wa;
    $pesan .= "\nTanggal Acara: " . $tanggal_acara;
    $pesan .= "\nPaket: " . $paket;
    $pesan .= "\nLokasi: " . $lokasi;
    $pesan .= "\nCatatan: " . ($catatan !== "" ? $catatan : "-");

    $pesan .= "\n\n*VENDOR YANG DIPILIH*";

    if (!empty($vendorTerpilih)) {

        $totalVendor = 0;

        foreach ($vendorTerpilih as $vendor) {

            $harga = (float) $vendor["harga_mulai"];
            $totalVendor += $harga;

            $pesan .= "\n- " . $vendor["nama_vendor"];
            $pesan .= " (Rp " . number_format($harga, 0, ',', '.') . ")";
        }

        $pesan .= "\n\nTotal estimasi vendor: Rp ";
        $pesan .= number_format($totalVendor, 0, ',', '.');

    } else {
        $pesan .= "\nBelum memilih vendor tambahan.";
    }

    $pesan .= "\n\nMohon informasi lebih lanjut mengenai proses booking.";
    $pesan .= "\nTerima kasih.";

    // ========================================
    // 9. REDIRECT KE WHATSAPP ADMIN
    // ========================================

    $urlWhatsApp = "https://wa.me/" .
        $nomorAdmin .
        "?text=" .
        rawurlencode($pesan);

    $conn->close();

    header("Location: " . $urlWhatsApp);
    exit;

} catch (Throwable $e) {

    // Membatalkan penyimpanan jika terjadi kesalahan
    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        error_log($rollbackError->getMessage());
    }

    error_log("WEDORA booking error: " . $e->getMessage());

    http_response_code(500);

    tampilkanError([
        "Maaf, booking belum dapat diproses. Silakan periksa kembali data dan coba lagi."
    ]);
}
?>
