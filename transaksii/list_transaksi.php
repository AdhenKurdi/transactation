<?php
include("koneksi.php");

// Fungsi untuk mendapatkan daftar metode pembayaran dari API Duitku
function getPaymentMethods() {
    // Set kode merchant Anda
    $merchantCode = "DS16785";
    // Set merchant key Anda
    $apiKey = "1ed94ac9218382a64f82e89c6121294c";

    // Data untuk permintaan ke API Duitku
    $data = array(
        'merchantcode' => $merchantCode,
        'amount' => 0, // Jumlah akan diisi setelah mendapatkan daftar metode pembayaran
        'datetime' => date('Y-m-d H:i:s'),
    );

    // Konversi data menjadi format JSON
    $data_json = json_encode($data);

    // URL API Duitku untuk mendapatkan daftar metode pembayaran
    $duitku_url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

    // Inisialisasi curl
    $ch = curl_init();

    // Set konfigurasi curl
    curl_setopt($ch, CURLOPT_URL, $duitku_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_json),
        'Authorization: ' . $apiKey, // Tambahkan header otorisasi
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Eksekusi permintaan ke API Duitku
    $response = curl_exec($ch);

    // Tutup curl
    curl_close($ch);

    // Proses respons dari API Duitku
    if ($response) {
        // Decode respons JSON dari API Duitku
        $result = json_decode($response, true);

        // Periksa apakah permintaan berhasil
        if (isset($result['statusCode']) && $result['statusCode'] == 00) {
            // Metode pembayaran berhasil diambil, kembalikan daftar metode pembayaran
            return $result['data'];
        }
    }

    return array(); // Kembalikan array kosong jika gagal
}

// Fungsi untuk menampilkan daftar metode pembayaran
function displayPaymentMethods($paymentMethods) {
    if (!empty($paymentMethods)) {
        echo "<h2>Pilih Metode Pembayaran</h2>";
        echo "<form action=\"konfirmasi_pembayaran.php\" method=\"GET\">";
        echo "<label for=\"metode_pembayaran\">Pilih Metode Pembayaran:</label>";
        echo "<select name=\"metode_pembayaran\">";

        foreach ($paymentMethods as $paymentMethod) {
            $paymentCode = $paymentMethod['paymentCode'];
            $paymentName = $paymentMethod['paymentName'];
            echo "<option value=\"$paymentCode\">$paymentName</option>";
        }

        echo "</select><br>";
        echo "<input type=\"submit\" value=\"Pilih Metode Pembayaran\">";
        echo "</form>";
    } else {
        echo "Gagal mengambil daftar metode pembayaran dari API Duitku.";
    }
}

$sql = "SELECT transaksi.id_transaksi, barang.nama_barang, users.nama_user, transaksi.jumlah, barang.harga, transaksi.created_at
        FROM transaksi
        JOIN barang ON transaksi.id_barang = barang.id_barang
        JOIN users ON transaksi.id_user = users.id";

$result = mysqli_query($conn, $sql);

// Inisialisasi total awal
$total = 0;

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>
    <tr>
        <th>ID Transaksi</th>
        <th>Nama Barang</th>
        <th>Nama User</th>
        <th>Jumlah</th>
        <th>Harga</th>
        <th>Subtotal</th>
        <th>Tanggal Transaksi</th>
        <th>Action</th> <!-- Kolom untuk tindakan pembayaran -->
    </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        $jumlah = $row["jumlah"];
        $harga = $row["harga"];
        $subtotal = $jumlah * $harga;

        // Akumulasikan subtotal ke total
        $total += $subtotal;

        echo "<tr>
            <td>" . $row["id_transaksi"] . "</td>
            <td>" . $row["nama_barang"] . "</td>
            <td>" . $row["nama_user"] . "</td>
            <td>" . $row["jumlah"] . "</td>
            <td>Rp " . number_format($row["harga"], 0, ",", ".") . "</td>
            <td>Rp " . number_format($subtotal, 0, ",", ".") . "</td>
            <td>" . $row["created_at"] . "</td>
            <td><a href='konfirmasi_pembayaran.php?id_transaksi=" . $row["id_transaksi"] . "&metode_pembayaran=duitku'>Bayar</a></td>
        </tr>";
    }

    // Tampilkan total di bawah kolom subtotal
    echo "<tr>
        <td colspan='5'></td>
        <td><strong>Rp " . number_format($total, 0, ",", ".") . "</strong></td>
        <td></td>
        <td></td>
    </tr>";

    echo "</table>";

    // Mendapatkan daftar metode pembayaran
    $paymentMethods = getPaymentMethods();

    if (count($paymentMethods) > 0) {
        // Menampilkan daftar metode pembayaran
        displayPaymentMethods($paymentMethods);
    }
} else {
    echo "Tidak ada transaksi.";
}

mysqli_close($conn);
?>
<p> <button class="add-button" onclick="location.href='tambah_transaksi_form.php'">Tambah Transaksi</button> </p>

<head>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table, th, td {
        border: 1px solid #ccc;
    }

    th, td {
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    th, td {
        white-space: nowrap;
    }

    .add-button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    .add-button:hover {
        background-color: #0056b3;
    }

</style>

</head>