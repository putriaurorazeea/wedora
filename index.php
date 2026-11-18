
<?php
require_once "koneksi.php";

// Paket layanan WEDORA
$paketList = [
    "Silver" => "Silver",
    "Gold" => "Gold",
    "Platinum" => "Platinum"
];

// Fungsi mengamankan output HTML
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ========================================
// MENGAMBIL VENDOR YANG DIPILIH
// ========================================

$vendorIds = [];
$vendorTerpilih = [];

$vendorInput = $_GET["vendor_ids"] ?? [];

if (!is_array($vendorInput)) {
    $vendorInput = explode(",", (string)$vendorInput);
}

foreach ($vendorInput as $id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0) {
        $vendorIds[] = (int)$id;
    }
}

$vendorIds = array_values(array_unique($vendorIds));

// Mengambil data vendor dari database
if (!empty($vendorIds)) {

    $placeholders = implode(",", array_fill(0, count($vendorIds), "?"));

    $sqlVendor = "SELECT id, nama_vendor, harga_mulai
                  FROM vendors
                  WHERE id IN ($placeholders)
                  AND status = 'aktif'";

    $stmtVendor = $conn->prepare($sqlVendor);

    if ($stmtVendor) {

        $types = str_repeat("i", count($vendorIds));
        $bindValues = [$types];

        foreach ($vendorIds as $key => $id) {
            $bindValues[] = &$vendorIds[$key];
        }

        call_user_func_array(
            [$stmtVendor, "bind_param"],
            $bindValues
        );

        $stmtVendor->execute();

        $resultVendor = $stmtVendor->get_result();
        $vendorTerpilih = $resultVendor->fetch_all(MYSQLI_ASSOC);

        $stmtVendor->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WEDORA | WO Rumah Kolaborasi</title>

    <meta name="description" content="WEDORA by Rumah Kolaborasi. Wujudkan pernikahan impian bersama wedding organizer profesional.">

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- WARNA WEDORA -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dusty: '#DCAE96',
                        rose: '#C08081',
                        deep: '#8B5A5A',
                        goldrose: '#B76E79',
                        ivory: '#FDFBF7',
                        cream: '#FFFDD0',
                        charcoal: '#332C2B'
                    },
                    fontFamily: {
                        serif: ['Georgia', 'serif'],
                        sans: ['Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #FDFBF7;
            color: #332C2B;
        }

        .hero-bg {
            background:
                linear-gradient(
                    rgba(51, 44, 43, 0.48),
                    rgba(51, 44, 43, 0.48)
                ),
                url('images/background.jpg');

            background-size: cover;
            background-position: center;
        }

        .section-title {
            font-family: Georgia, serif;
            color: #8B5A5A;
        }

        .package-card {
            transition: transform 0.3s ease,
                        box-shadow 0.3s ease;
        }

        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(139, 90, 90, 0.15);
        }

        .rose-button {
            background-color: #C08081;
            color: white;
            transition: 0.3s;
        }

        .rose-button:hover {
            background-color: #8B5A5A;
        }
    </style>
</head>

<body class="font-sans">

<!-- =====================================
     NAVBAR
====================================== -->

<nav class="bg-ivory/95 backdrop-blur-md shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        
<a href="#home" class="flex items-center">
    <img
        src="images/logo_wedora.jpeg"
        alt="Logo WEDORA"
        class="h-12 md:h-14 w-auto object-contain mix-blend-multiply"
    >
</a>


        <div class="hidden md:flex items-center gap-8 text-sm font-medium">
            <a href="#home" class="hover:text-rose">Home</a>
            <a href="#about" class="hover:text-rose">Tentang Kami</a>
            <a href="#packages" class="hover:text-rose">Paket</a>
            <a href="vendor.php">Vendor</a>
            <a href="#gallery" class="hover:text-rose">Galeri</a>
            <a href="#booking" class="hover:text-rose">Booking</a>
        </div>

        <a href="#booking"
           class="rose-button px-5 py-2 rounded-full text-sm font-semibold">
            Booking Sekarang
        </a>

    </div>
</nav>


<!-- =====================================
     HERO SECTION
====================================== -->

<section id="home"
    class="hero-bg min-h-[600px] flex items-center justify-center text-center px-6">

    <div class="max-w-3xl text-white">

        <p class="uppercase tracking-[5px] text-sm mb-6">
            WO Rumah Kolaborasi Presents
        </p>

        <h1 class="font-serif text-5xl md:text-7xl leading-tight mb-6">
            Every Love Story
            <span class="italic text-dusty">Deserves</span>
            a Beautiful Beginning
        </h1>

        <p class="text-base md:text-lg leading-relaxed mb-10 text-white/90">
            Kami hadir untuk menciptakan momen pernikahan
            yang indah, berkesan, dan penuh cerita.
            Percayakan hari istimewamu bersama WEDORA.
        </p>

        <a href="#booking"
           class="inline-block bg-dusty hover:bg-rose text-white
                  px-9 py-4 rounded-full font-semibold transition">
            Booking Sekarang
        </a>

    </div>
</section>


<!-- =====================================
     TENTANG KAMI
====================================== -->

<section id="about" class="py-24 px-6">

    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-14 items-center">

        <div>
            <img
                src="images/wedding1.jpg"
                alt="Dokumentasi pernikahan Rumah Kolaborasi"
                class="w-full h-[450px] object-cover rounded-2xl shadow-lg"
            >
        </div>

        <div>

            <p class="uppercase tracking-[4px] text-rose text-sm mb-4">
                About WEDORA
            </p>

            <h2 class="section-title text-4xl md:text-5xl mb-7">
                Your Dream Wedding, Our Passion
            </h2>

            <p class="text-gray-600 leading-8 mb-5">
                WEDORA merupakan layanan wedding organizer
                dari WO Rumah Kolaborasi yang hadir untuk
                membantu setiap pasangan mewujudkan
                pernikahan impian mereka.
            </p>

            <p class="text-gray-600 leading-8 mb-7">
                Kami percaya bahwa setiap pernikahan memiliki
                cerita yang istimewa. Melalui perencanaan yang
                matang, kreativitas, dan kerja sama tim,
                kami berkomitmen menghadirkan pengalaman
                pernikahan yang berkesan dan penuh kebahagiaan.
            </p>

            <a href="#packages"
               class="inline-block border border-rose text-deep
                      px-7 py-3 rounded-full hover:bg-rose
                      hover:text-white transition">
                Lihat Paket Kami
            </a>

        </div>

    </div>

</section>


<!-- =====================================
     PAKET LAYANAN
====================================== -->

<section id="packages" class="py-24 px-6 bg-[#F7EDE7]">

    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16">

            <p class="uppercase tracking-[4px] text-rose text-sm mb-4">
                Our Services
            </p>

            <h2 class="section-title text-4xl md:text-5xl mb-5">
                Choose Your Perfect Package
            </h2>

            <p class="text-gray-600 max-w-2xl mx-auto">
                Pilih paket layanan yang sesuai dengan
                kebutuhan dan impian pernikahan Anda.
            </p>

        </div>

        <div class="grid md:grid-cols-3 gap-8">

            <!-- SILVER -->

            <div class="package-card bg-ivory p-9 rounded-2xl shadow-sm">

                <p class="text-rose uppercase tracking-[3px] text-sm">
                    Package 01
                </p>

                <h3 class="font-serif text-3xl text-deep mt-3">
                    Silver
                </h3>

                <p class="text-gray-500 text-sm mt-3">
                    Pilihan praktis untuk pernikahan sederhana.
                </p>

                <h4 class="text-3xl font-bold text-charcoal mt-7">
                    Rp8.000.000
                </h4>

                <p class="text-xs text-gray-400 mt-1">
                    Harga awal / paket
                </p>

                <hr class="my-7 border-dusty">

                <ul class="space-y-4 text-sm text-gray-600">
                    <li>✓ Konsultasi persiapan pernikahan</li>
                    <li>✓ 3 orang tim wedding organizer</li>
                    <li>✓ Koordinasi vendor</li>
                    <li>✓ Rundown acara</li>
                    <li>✓ Pendampingan hari H</li>
                </ul>

                <a href="#booking"
                   data-package="Silver"
                   class="choose-package block text-center mt-9
                          border border-rose text-deep py-3 rounded-full
                          hover:bg-rose hover:text-white transition">
                    Pilih Paket Silver
                </a>

            </div>


            <!-- GOLD -->

            <div class="package-card bg-white p-9 rounded-2xl
                        shadow-xl border-2 border-dusty relative">

                <div class="absolute -top-4 left-1/2 -translate-x-1/2
                            bg-rose text-white px-6 py-2 rounded-full
                            text-xs tracking-wider">
                    MOST POPULAR
                </div>

                <p class="text-rose uppercase tracking-[3px] text-sm">
                    Package 02
                </p>

                <h3 class="font-serif text-3xl text-deep mt-3">
                    Gold
                </h3>

                <p class="text-gray-500 text-sm mt-3">
                    Paket lengkap untuk momen lebih istimewa.
                </p>

                <h4 class="text-3xl font-bold text-charcoal mt-7">
                    Rp15.000.000
                </h4>

                <p class="text-xs text-gray-400 mt-1">
                    Harga awal / paket
                </p>

                <hr class="my-7 border-dusty">

                <ul class="space-y-4 text-sm text-gray-600">
                    <li>✓ Semua fasilitas Silver</li>
                    <li>✓ 5 orang tim wedding organizer</li>
                    <li>✓ Koordinasi vendor lebih lengkap</li>
                    <li>✓ Pengaturan tamu undangan</li>
                    <li>✓ Koordinasi keluarga</li>
                    <li>✓ Pendampingan hari H</li>
                </ul>

                <a href="#booking"
                   data-package="Gold"
                   class="choose-package rose-button block text-center
                          mt-9 py-3 rounded-full">
                    Pilih Paket Gold
                </a>

            </div>


            <!-- PLATINUM -->

            <div class="package-card bg-ivory p-9 rounded-2xl shadow-sm">

                <p class="text-rose uppercase tracking-[3px] text-sm">
                    Package 03
                </p>

                <h3 class="font-serif text-3xl text-deep mt-3">
                    Platinum
                </h3>

                <p class="text-gray-500 text-sm mt-3">
                    Pengalaman premium untuk hari istimewa.
                </p>

                <h4 class="text-3xl font-bold text-charcoal mt-7">
                    Rp25.000.000
                </h4>

                <p class="text-xs text-gray-400 mt-1">
                    Harga awal / paket
                </p>

                <hr class="my-7 border-dusty">

                <ul class="space-y-4 text-sm text-gray-600">
                    <li>✓ Semua fasilitas Gold</li>
                    <li>✓ 8 orang tim wedding organizer</li>
                    <li>✓ Perencanaan acara menyeluruh</li>
                    <li>✓ Koordinasi vendor premium</li>
                    <li>✓ Pendampingan keluarga VIP</li>
                    <li>✓ Evaluasi dan koordinasi acara</li>
                </ul>

                <a href="#booking"
                   data-package="Platinum"
                   class="choose-package block text-center mt-9
                          border border-rose text-deep py-3 rounded-full
                          hover:bg-rose hover:text-white transition">
                    Pilih Paket Platinum
                </a>

            </div>

        </div>

        <p class="text-center text-xs text-gray-500 mt-8">
            *Harga dan fasilitas merupakan contoh awal
            yang dapat disesuaikan oleh WO Rumah Kolaborasi.
        </p>

    </div>

</section>


<!-- =====================================
     GALERI / PORTOFOLIO
====================================== -->

<section id="gallery" class="py-24 px-6">

    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-14">

            <p class="uppercase tracking-[4px] text-rose text-sm mb-4">
                Our Memories
            </p>

            <h2 class="section-title text-4xl md:text-5xl">
                Beautiful Moments
            </h2>

            <p class="text-gray-500 mt-5">
                Setiap detail memiliki cerita,
                setiap momen menjadi kenangan.
            </p>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <img
                src="images/decor.jpg"
                alt="Dokumentasi wedding 1"
                class="w-full h-80 object-cover rounded-xl"
                loading="lazy"
            >

            <img
                src="images/wedding2.jpg"
                alt="Dokumentasi wedding 2"
                class="w-full h-80 object-cover rounded-xl"
                loading="lazy"
            >

            <img
                src="images/wedding3.jpg"
                alt="Dokumentasi wedding 3"
                class="w-full h-80 object-cover rounded-xl"
                loading="lazy"
            >

        </div>

    </div>

</section>


<!-- =====================================
     FORM BOOKING
====================================== -->

<section id="booking" class="py-24 px-6 bg-[#F7EDE7]">

    <div class="max-w-4xl mx-auto">

        <div class="text-center mb-12">

            <p class="uppercase tracking-[4px] text-rose text-sm mb-4">
                Reservation
            </p>

            <h2 class="section-title text-4xl md:text-5xl mb-5">
                Start Your Love Story
            </h2>

            <p class="text-gray-600">
                Isi formulir berikut untuk memulai konsultasi
                dan pemesanan paket pernikahan bersama kami.
            </p>

        </div>

        <div class="bg-white p-7 md:p-12 rounded-2xl shadow-lg">

            <form action="process-booking.php"
                  method="POST"
                  id="bookingForm">

                <!-- VENDOR YANG DIPILIH -->

                <?php if (!empty($vendorIds)): ?>

                    <div class="mb-8 rounded-xl border border-dusty bg-ivory p-5">

                        <h3 class="font-serif text-xl text-deep mb-3">
                            Vendor Pilihan Anda
                        </h3>

                        <?php if (!empty($vendorTerpilih)): ?>

                            <ul class="space-y-3">

                                <?php foreach ($vendorTerpilih as $vendor): ?>

                                    <li class="flex flex-wrap justify-between gap-2 text-sm text-gray-700">

                                        <span>
                                            <?php echo e($vendor["nama_vendor"]); ?>
                                        </span>

                                        <span class="font-semibold text-deep">
                                            Rp<?php echo number_format((float)$vendor["harga_mulai"], 0, ",", "."); ?>
                                        </span>

                                    </li>

                                    <input
                                        type="hidden"
                                        name="vendor_ids[]"
                                        value="<?php echo (int)$vendor["id"]; ?>"
                                    >

                                <?php endforeach; ?>

                            </ul>

                            <p class="border-t border-dusty mt-4 pt-3 text-xs text-gray-500">
                                Vendor pilihan akan ikut dikirim bersama formulir booking.
                            </p>

                        <?php else: ?>

                            <p class="text-sm text-gray-600">
                                Vendor pilihan tidak ditemukan atau sudah tidak aktif.
                                Silakan kembali ke katalog vendor dan pilih ulang.
                            </p>

                        <?php endif; ?>

                        <a href="vendor.php"
                           class="inline-block mt-4 text-sm text-deep underline hover:text-rose">
                            Kembali ke Katalog Vendor
                        </a>

                    </div>

                <?php else: ?>

                    <div class="mb-8 rounded-xl border border-dusty bg-ivory p-5">

                        <h3 class="font-serif text-xl text-deep mb-2">
                            Belum Memilih Vendor
                        </h3>

                        <p class="text-sm text-gray-600">
                            Anda bisa memilih vendor terlebih dahulu melalui katalog vendor.
                        </p>

                        <a href="vendor.php"
                           class="inline-block mt-3 text-sm text-deep underline hover:text-rose">
                            Lihat Katalog Vendor
                        </a>

                    </div>

                <?php endif; ?>


                <!-- NAMA -->

                <div class="mb-6">

                    <label for="nama"
                           class="block text-sm font-semibold mb-2">
                        Nama Lengkap *
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        maxlength="100"
                        required
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap Anda"
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    >

                </div>


                <!-- NOMOR WHATSAPP -->

                <div class="mb-6">

                    <label for="nomor_wa"
                           class="block text-sm font-semibold mb-2">
                        Nomor WhatsApp *
                    </label>

                    <input
                        type="tel"
                        name="nomor_wa"
                        id="nomor_wa"
                        maxlength="20"
                        required
                        placeholder="Contoh: 081234567890"
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    >

                    <p class="text-xs text-gray-400 mt-2">
                        Gunakan nomor WhatsApp yang aktif.
                    </p>

                </div>


                <!-- TANGGAL ACARA -->

                <div class="mb-6">

                    <label for="tanggal_acara"
                           class="block text-sm font-semibold mb-2">
                        Tanggal Acara *
                    </label>

                    <input
                        type="date"
                        name="tanggal_acara"
                        id="tanggal_acara"
                        required
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    >

                </div>


                <!-- PILIHAN PAKET -->

                <div class="mb-6">

                    <label for="paket"
                           class="block text-sm font-semibold mb-2">
                        Pilihan Paket *
                    </label>

                    <select
                        name="paket"
                        id="paket"
                        required
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    >

                        <option value="">-- Pilih Paket --</option>

                        <?php foreach ($paketList as $value): ?>

                            <option value="<?php echo e($value); ?>">
                                <?php echo e($value); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- LOKASI -->

                <div class="mb-6">

                    <label for="lokasi"
                           class="block text-sm font-semibold mb-2">
                        Lokasi Acara *
                    </label>

                    <input
                        type="text"
                        name="lokasi"
                        id="lokasi"
                        maxlength="255"
                        required
                        placeholder="Contoh: Purwokerto, Banyumas"
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    >

                </div>


                <!-- CATATAN -->

                <div class="mb-8">

                    <label for="catatan"
                           class="block text-sm font-semibold mb-2">
                        Catatan Tambahan
                    </label>

                    <textarea
                        name="catatan"
                        id="catatan"
                        rows="5"
                        maxlength="3000"
                        placeholder="Ceritakan kebutuhan atau konsep pernikahan Anda..."
                        class="w-full border border-gray-200 rounded-lg
                               px-4 py-3 outline-none focus:border-rose"
                    ></textarea>

                </div>


                <!-- TOMBOL SUBMIT -->

                <button
                    type="submit"
                    class="rose-button w-full py-4 rounded-full
                           font-semibold text-base shadow-md"
                >
                    Kirim Booking & Lanjut WhatsApp
                </button>

                <p class="text-center text-xs text-gray-400 mt-5">
                    Dengan mengirim formulir, Anda menyetujui
                    data booking diproses oleh WO Rumah Kolaborasi.
                </p>

            </form>

        </div>

    </div>

</section>


<!-- =====================================
     FOOTER
====================================== -->

<footer class="bg-[#332C2B] text-white py-12 px-6">

    <div class="max-w-7xl mx-auto text-center">

        <h2 class="font-serif text-4xl text-dusty font-bold">
            WEDORA.
        </h2>

        <p class="text-gray-300 mt-4">
            WO Rumah Kolaborasi
        </p>

        <p class="text-gray-400 text-sm mt-3">
            Creating beautiful moments,
            making memories forever.
        </p>

        <div class="flex justify-center flex-wrap gap-7 mt-8 text-sm">

            <a href="#home" class="hover:text-dusty">Home</a>
            <a href="#about" class="hover:text-dusty">About</a>
            <a href="#packages" class="hover:text-dusty">Packages</a>
            <a href="#gallery" class="hover:text-dusty">Gallery</a>
            <a href="#booking" class="hover:text-dusty">Booking</a>

        </div>

        <hr class="border-gray-600 my-8">

        <p class="text-xs text-gray-400">
            © <?php echo date("Y"); ?> WEDORA - WO Rumah Kolaborasi.
            All Rights Reserved.
        </p>

    </div>

</footer>


<!-- =====================================
     JAVASCRIPT
====================================== -->

<script>

// Mengatur tanggal minimal booking menjadi hari ini

const tanggalInput = document.getElementById('tanggal_acara');

const today = new Date();
const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, '0');
const day = String(today.getDate()).padStart(2, '0');

tanggalInput.min = `${year}-${month}-${day}`;


// Tombol pilih paket otomatis mengisi form

const packageButtons = document.querySelectorAll('.choose-package');

packageButtons.forEach(function(button) {

    button.addEventListener('click', function() {

        const selectedPackage = this.getAttribute('data-package');

        document.getElementById('paket').value = selectedPackage;

    });

});

</script>

</body>
</html>
