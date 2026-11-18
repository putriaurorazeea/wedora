
<?php
require_once 'koneksi.php';

// Pengaman output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Ambil kategori vendor
$categories = $conn->query("
    SELECT id, nama_kategori
    FROM vendor_categories
    ORDER BY nama_kategori ASC
");

// Filter kategori
$categoryId = isset($_GET['kategori']) && ctype_digit($_GET['kategori'])
    ? (int)$_GET['kategori']
    : 0;

// Pencarian
$search = trim($_GET['q'] ?? '');

// Query vendor aktif
$sql = "
    SELECT
        v.id,
        v.nama_vendor,
        v.deskripsi,
        v.harga_mulai,
        v.lokasi,
        v.nomor_wa,
        v.foto,
        vc.nama_kategori
    FROM vendors v
    INNER JOIN vendor_categories vc
        ON v.category_id = vc.id
    WHERE v.status = 'aktif'
";

if ($categoryId > 0 && $search !== '') {
    $sql .= " AND v.category_id = ?
              AND (v.nama_vendor LIKE ?
              OR v.deskripsi LIKE ?
              OR vc.nama_kategori LIKE ?)";
} elseif ($categoryId > 0) {
    $sql .= " AND v.category_id = ?";
} elseif ($search !== '') {
    $sql .= " AND (v.nama_vendor LIKE ?
              OR v.deskripsi LIKE ?
              OR vc.nama_kategori LIKE ?)";
}

$sql .= " ORDER BY vc.nama_kategori, v.nama_vendor";

$stmt = $conn->prepare($sql);

if ($categoryId > 0 && $search !== '') {
    $like = "%$search%";
    $stmt->bind_param("isss", $categoryId, $like, $like, $like);
} elseif ($categoryId > 0) {
    $stmt->bind_param("i", $categoryId);
} elseif ($search !== '') {
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$vendors = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Katalog Vendor | WEDORA</title>

<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                rose: {
                    main: '#DCAE96',
                    dark: '#C08081',
                    deep: '#8B5A5A',
                    gold: '#B76E79'
                },
                cream: '#FDFBF7',
                charcoal: '#332C2B'
            },
            fontFamily: {
                elegant: ['Georgia', 'serif']
            }
        }
    }
}
</script>

<style>
body {
    background: #FDFBF7;
    color: #332C2B;
}

html {
    scroll-behavior: smooth;
}

.vendor-card {
    transition: .3s ease;
}

.vendor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(139,90,90,.12);
}

.vendor-check:checked + label {
    background: #8B5A5A;
    color: white;
    border-color: #8B5A5A;
}

.category-active {
    background: #8B5A5A;
    color: white;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="bg-white/95 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <a href="index.php" class="flex items-center">
    <img
        src="images/logo_wedora.jpeg"
        alt="Logo WEDORA"
        class="h-12 md:h-14 w-auto object-contain"
    >
</a>
        <div class="hidden md:flex items-center gap-8 text-sm">

            <a href="index.php" class="hover:text-rose-dark">
                Beranda
            </a>

            <a href="index.php#about" class="hover:text-rose-dark">
                Tentang Kami
            </a>

            <a href="vendor.php" class="text-rose-deep font-semibold">
                Vendor
            </a>

            <a href="index.php#booking" class="hover:text-rose-dark">
                Booking
            </a>

        </div>

        <a href="index.php#booking"
           class="bg-rose-deep text-white px-5 py-2 rounded-full text-sm hover:bg-rose-dark">
            Booking
        </a>

    </div>
</nav>


<!-- HERO -->

<!-- HERO -->

<!-- HERO -->
<section
    class="relative min-h-[620px] flex items-center justify-center px-6 py-24 text-center overflow-hidden"
    style="
        background-image:
        linear-gradient(
            rgba(253, 247, 243, 0.30),
            rgba(220, 174, 150, 0.25)
        ),
        url('https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=2000&q=90');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    "
>

    <div class="relative z-10 max-w-4xl mx-auto">

        <p class="uppercase tracking-[5px] text-[#8B5A5A] text-xs md:text-sm mb-6 font-bold">
            WEDORA - Rumah Kolaborasi
        </p>

        <h1 class="text-4xl md:text-7xl font-elegant text-[#332C2B] mb-7 leading-tight drop-shadow-sm">
            Temukan Vendor Impianmu
        </h1>

        <p class="max-w-2xl mx-auto text-[#332C2B] text-base md:text-xl leading-relaxed font-medium">
            Pilih vendor pernikahan terbaik untuk mewujudkan
            hari istimewa yang indah dan tak terlupakan.
        </p>

        <div class="mt-10">
            <a
                href="#catalog"
                class="inline-block bg-[#8B5A5A] text-white px-10 py-4 rounded-full hover:bg-[#C08081] transition duration-300 shadow-lg"
            >
                Jelajahi Vendor
            </a>
        </div>

    </div>

</section>




<!-- KATALOG VENDOR -->
<section id="catalog" class="max-w-7xl mx-auto px-6 py-16">

    <div class="text-center mb-12">

        <p class="text-rose-dark uppercase tracking-[4px] text-xs mb-3">
            Our Wedding Partners
        </p>

        <h2 class="text-3xl md:text-4xl font-elegant mb-4">
            Katalog Vendor
        </h2>

        <p class="text-gray-500">
            Temukan dan pilih vendor sesuai kebutuhan pernikahanmu.
        </p>

    </div>


    <!-- PENCARIAN -->
    <form method="GET"
          action="vendor.php"
          class="max-w-2xl mx-auto mb-10">

        <div class="flex bg-white border border-[#E9D5CD] rounded-full overflow-hidden shadow-sm">

            <input
                type="text"
                name="q"
                value="<?= e($search) ?>"
                placeholder="Cari nama vendor atau layanan..."
                class="flex-1 px-6 py-4 outline-none text-sm min-w-0"
            >

            <?php if ($categoryId > 0): ?>
                <input type="hidden" name="kategori"
                       value="<?= $categoryId ?>">
            <?php endif; ?>

            <button type="submit"
                    class="bg-rose-deep text-white px-7 hover:bg-rose-dark">
                Cari
            </button>

        </div>

    </form>


    <!-- FILTER KATEGORI -->
    <div class="mb-10">

        <h3 class="font-semibold text-center mb-5">
            Pilih Kategori Vendor
        </h3>

        <div class="flex flex-wrap justify-center gap-3">

            <a href="vendor.php"
               class="px-5 py-2 rounded-full border text-sm
               <?= $categoryId === 0
                    ? 'category-active'
                    : 'border-rose-main hover:bg-rose-100' ?>">
                Semua Vendor
            </a>

            <?php while ($cat = $categories->fetch_assoc()): ?>

                <a href="vendor.php?kategori=<?= (int)$cat['id'] ?>"
                   class="px-5 py-2 rounded-full border text-sm transition
                   <?= $categoryId === (int)$cat['id']
                        ? 'category-active'
                        : 'border-rose-main hover:bg-rose-100' ?>">

                    <?= e($cat['nama_kategori']) ?>

                </a>

            <?php endwhile; ?>

        </div>

    </div>


    <!-- JUMLAH VENDOR -->
    <div class="flex justify-between items-center mb-6">

        <h3 class="font-semibold text-lg">
            Pilihan Vendor
        </h3>

        <span class="text-sm text-gray-500">
            <?= $vendors->num_rows ?> vendor ditemukan
        </span>

    </div>


    <!-- KARTU VENDOR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

        <?php if ($vendors->num_rows > 0): ?>

            <?php while ($vendor = $vendors->fetch_assoc()): ?>

                <div class="vendor-card bg-white rounded-2xl overflow-hidden border border-[#F0E3DC] shadow-sm">

                    <!-- FOTO -->
                    <div class="relative h-64 bg-[#F3E5DE]">

                        <?php if (!empty($vendor['foto'])): ?>

                            <img
                                src="<?= e($vendor['foto']) ?>"
                                alt="<?= e($vendor['nama_vendor']) ?>"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none';"
                            >

                        <?php endif; ?>

                        <span class="absolute top-4 left-4 bg-white/95 text-rose-deep text-xs px-4 py-2 rounded-full shadow">
                            <?= e($vendor['nama_kategori']) ?>
                        </span>

                    </div>


                    <!-- INFORMASI VENDOR -->
                    <div class="p-6">

                        <h3 class="text-xl font-elegant font-semibold mb-2">
                            <?= e($vendor['nama_vendor']) ?>
                        </h3>

                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">
                            <?= e($vendor['deskripsi']) ?>
                        </p>

                        <p class="text-xs text-gray-400 mb-4">
                            Lokasi: <?= e($vendor['lokasi']) ?>
                        </p>

                        <div class="border-t border-gray-100 pt-4 mb-5">

                            <p class="text-xs text-gray-400">
                                Harga mulai dari
                            </p>

                            <p class="text-xl font-bold text-rose-deep">
                                Rp <?= number_format(
                                    (float)$vendor['harga_mulai'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </p>

                        </div>


                        <!-- TOMBOL DETAIL DAN PILIH -->
                        <div class="flex gap-3">

                            <a href="vendor.detail.php?id=<?= (int)$vendor['id'] ?>"
                               class="flex-1 text-center border border-rose-deep text-rose-deep py-3 rounded-xl text-sm hover:bg-rose-50 transition">

                                Detail

                            </a>

                            <div class="flex-1">

                                <input
                                    type="checkbox"
                                    id="vendor-<?= (int)$vendor['id'] ?>"
                                    class="vendor-check hidden"
                                    data-id="<?= (int)$vendor['id'] ?>"
                                    data-name="<?= e($vendor['nama_vendor']) ?>"
                                    data-price="<?= (float)$vendor['harga_mulai'] ?>"
                                >

                                <label
                                    for="vendor-<?= (int)$vendor['id'] ?>"
                                    class="block text-center border border-rose-deep text-rose-deep py-3 rounded-xl text-sm cursor-pointer hover:bg-rose-50 transition">

                                    + Pilih

                                </label>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-span-full text-center py-16">

                <p class="text-5xl mb-4">♡</p>

                <h3 class="text-xl font-elegant mb-2">
                    Vendor belum ditemukan
                </h3>

                <p class="text-gray-500 mb-5">
                    Coba gunakan kata kunci atau kategori lainnya.
                </p>

                <a href="vendor.php"
                   class="text-rose-deep underline">
                    Lihat semua vendor
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- KERANJANG VENDOR -->
<section class="bg-[#F3E5DE] py-14 px-6">

    <div class="max-w-5xl mx-auto bg-white rounded-3xl p-7 md:p-10 shadow-sm">

        <div class="text-center mb-8">

            <p class="text-xs tracking-[4px] uppercase text-rose-dark mb-3">
                Your Selection
            </p>

            <h2 class="text-3xl font-elegant">
                Vendor Pilihanmu
            </h2>

            <p class="text-sm text-gray-500 mt-2">
                Kumpulkan vendor favoritmu sebelum melanjutkan booking.
            </p>

        </div>


        <div id="empty-selection"
             class="text-center text-gray-400 py-6">

            Belum ada vendor yang dipilih.

        </div>


        <div id="selected-list" class="space-y-3"></div>


        <div class="border-t border-gray-200 mt-7 pt-6">

            <div class="flex justify-between mb-5">

                <span class="font-semibold">
                    Total Estimasi Vendor
                </span>

                <span id="total-price"
                      class="text-xl font-bold text-rose-deep">
                    Rp 0
                </span>

            </div>

            <p class="text-xs text-gray-400 mb-5">
                Harga merupakan estimasi awal dan dapat berubah
                sesuai kesepakatan dengan vendor.
            </p>

            <button
                type="button"
                id="continue-booking"
                class="w-full bg-rose-deep text-white py-4 rounded-xl font-semibold hover:bg-rose-dark transition">

                Lanjutkan ke Booking →

            </button>

        </div>

    </div>

</section>


<!-- FOOTER -->
<footer class="bg-[#332C2B] text-white py-10 px-6 text-center">

    <h2 class="text-3xl font-elegant mb-3">
        WEDORA
    </h2>

    <p class="text-sm text-gray-300 mb-4">
        WO Rumah Kolaborasi
    </p>

    <p class="text-xs text-gray-400">
        Every Love Story Deserves a Beautiful Beginning.
    </p>

    <p class="text-xs text-gray-500 mt-6">
        © <?= date('Y') ?> WEDORA. All Rights Reserved.
    </p>

</footer>


<!-- JAVASCRIPT -->
<script>

// Kunci penyimpanan vendor
const STORAGE_KEY = 'wedora_selected_vendors';

// Mengambil data vendor yang tersimpan
let selectedVendors = {};

try {
    selectedVendors = JSON.parse(
        sessionStorage.getItem(STORAGE_KEY) || '{}'
    ) || {};
} catch (error) {
    selectedVendors = {};
}


// Format rupiah
function rupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}


// Simpan pilihan vendor
function saveSelection() {
    sessionStorage.setItem(
        STORAGE_KEY,
        JSON.stringify(selectedVendors)
    );
}


// Update keranjang vendor
function updateSelection() {

    const list = document.getElementById('selected-list');
    const empty = document.getElementById('empty-selection');
    const totalElement = document.getElementById('total-price');

    const vendors = Object.values(selectedVendors);

    list.innerHTML = '';

    let total = 0;

    empty.style.display = vendors.length ? 'none' : 'block';

    vendors.forEach(vendor => {

        total += Number(vendor.price);

        const item = document.createElement('div');

        item.className =
            'flex justify-between items-center gap-3 bg-[#FDFBF7] p-4 rounded-xl';

        const info = document.createElement('div');

        const name = document.createElement('p');

        name.className = 'font-semibold text-sm';

        name.textContent = vendor.name;

        const price = document.createElement('p');

        price.className = 'text-xs text-rose-deep mt-1';

        price.textContent = rupiah(vendor.price);

        info.appendChild(name);

        info.appendChild(price);


        // Tombol hapus
        const remove = document.createElement('button');

        remove.type = 'button';

        remove.textContent = 'Hapus';

        remove.className =
            'text-xs text-red-500 hover:underline shrink-0';

        remove.addEventListener('click', function() {

            delete selectedVendors[vendor.id];

            const checkbox = document.getElementById(
                'vendor-' + vendor.id
            );

            if (checkbox) {
                checkbox.checked = false;
            }

            saveSelection();

            updateSelection();

        });


        item.appendChild(info);

        item.appendChild(remove);

        list.appendChild(item);

    });

    totalElement.textContent = rupiah(total);

}


// Mengaktifkan checkbox vendor
document.querySelectorAll('.vendor-check').forEach(checkbox => {

    const id = checkbox.dataset.id;

    // Pulihkan pilihan sebelumnya
    if (selectedVendors[id]) {
        checkbox.checked = true;
    }

    checkbox.addEventListener('change', function() {

        if (this.checked) {

            selectedVendors[id] = {

                id: id,

                name: this.dataset.name,

                price: Number(this.dataset.price)

            };

        } else {

            delete selectedVendors[id];

        }

        saveSelection();

        updateSelection();

    });

});


// Tombol lanjut booking
document.getElementById('continue-booking')
.addEventListener('click', function() {

    const ids = Object.keys(selectedVendors);

    // Validasi pilihan
    if (ids.length === 0) {

        alert('Silakan pilih minimal satu vendor terlebih dahulu.');

        return;

    }

    // Ambil semua ID vendor
    const vendorIds = ids.join(',');

    // Arahkan ke halaman booking
    window.location.href =
        'index.php?vendor_ids=' +
        encodeURIComponent(vendorIds) +
        '#booking';

});


// Tampilkan keranjang saat halaman dibuka
updateSelection();

</script>

</body>
</html>
