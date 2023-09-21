<?php
include("koneksi.php");

// Ambil ID transaksi dari URL
$id_transaksi = $_GET["id_transaksi"];

// Query untuk mengambil data transaksi
$sql_transaksi = "SELECT transaksi.id_transaksi, barang.nama_barang, transaksi.jumlah, barang.harga
                  FROM transaksi
                  JOIN barang ON transaksi.id_barang = barang.id_barang
                  WHERE transaksi.id_transaksi = $id_transaksi";

$result_transaksi = mysqli_query($conn, $sql_transaksi);

if (!$result_transaksi) {
    die("Error in SQL query: " . mysqli_error($conn));
}

// Menghitung subtotal berdasarkan data transaksi
$subtotal = 0;

if (mysqli_num_rows($result_transaksi) > 0) {
    while ($row_transaksi = mysqli_fetch_assoc($result_transaksi)) {
        $harga = $row_transaksi["harga"];
        $jumlah = $row_transaksi["jumlah"];
        $subtotal += $harga * $jumlah;
    }
}

// Periksa apakah parameter "metode_pembayaran" ada dalam argumen URL
if (isset($_GET["metode_pembayaran"])) {
    $metode_pembayaran = $_GET["metode_pembayaran"];
    
    // Mendapatkan daftar metode pembayaran dari API Duitku
    if ($metode_pembayaran === "duitku") {
        // Set kode merchant Anda
        $merchantCode = "DS16785";
        // Set merchant key Anda
        $apiKey = "1ed94ac9218382a64f82e89c6121294c";

        $datetime = date('Y-m-d H:i:s');

        $sha256Hash = hash('sha256', $merchantCode . $subtotal . $datetime . $apiKey );
       
        $md5hash = "ff6b3fbcd8376656873ee63d374aa81f";


        // Data untuk permintaan ke API Duitku
        $data = array(
            'merchantcode' => $merchantCode,
            'amount' => $subtotal, // Sesuaikan dengan jumlah yang benar
            'datetime' => $datetime,
            'signature' => $sha256Hash
        );

        $selectedPaymentMethod = '';

    
        // Konversi data menjadi format JSON
        $data_json = json_encode($data);

       

        $duitku_url1 = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
  

        // Inisialisasi curl untuk URL pertama
        $ch1 = curl_init();

        // Set konfigurasi curl untuk URL pertama
        curl_setopt($ch1, CURLOPT_URL, $duitku_url1);
        curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
            'Authorization: ' . $apiKey, // Tambahkan header otorisasi untuk URL pertama
        ));
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);

        // Eksekusi permintaan ke URL pertama
        $response = curl_exec($ch1);

        // Tutup curl untuk URL pertama
        curl_close($ch1);

        
        // Proses respons dari API Duitku
        


       
        // if (!empty($result) && isset($result)) {
        //     // Metode pembayaran berhasil diambil, tampilkan daftar metode pembayaran
        //     $availablePaymentMethods = $result;

        //     echo "<h3>Metode Pembayaran yang Tersedia:</h3>";

        //     if (!empty($availablePaymentMethods)) {
        //         echo "<ul>";
        //         foreach ($availablePaymentMethods as $paymentMethod) {
        //             echo "<li>";
        //             echo "Metode Pembayaran: " . $paymentMethod['paymentName'];
        //             echo "<br>";
        //             echo "Total Fee: " . $paymentMethod['totalFee'];
        //             echo "<br>";
        //             echo "<img src='" . $paymentMethod['paymentImage'] . "' alt='" . $paymentMethod['paymentName'] . "'>";
        //             echo "</li>";
        //         }
        //         echo "</ul>";
        //     } else {
        //         echo "Tidak ada metode pembayaran yang tersedia.";
        //     }
        // } else {
        //     // Menampilkan pesan kesalahan jika permintaan gagal
        //     echo "Gagal mengambil daftar metode pembayaran dari API Duitku.";
        // }
    }
}

// Tampilkan data transaksi
echo "<h2>Detail Transaksi</h2>";
echo "<p>ID Transaksi: $id_transaksi</p>";

if (mysqli_num_rows($result_transaksi) > 0) {
    while ($row_transaksi = mysqli_fetch_assoc($result_transaksi)) {
        $nama_barang = $row_transaksi["nama_barang"];
        $jumlah = $row_transaksi["jumlah"];
        $harga = $row_transaksi["harga"];
        $total_harga = $harga * $jumlah;

        echo "<p>Nama Barang: $nama_barang</p>";
        echo "<p>Jumlah: $jumlah</p>";
        echo "<p>Harga: Rp " . number_format($harga, 0, ",", ".") . "</p>";
        echo "<p>Total Harga: Rp " . number_format($total_harga, 0, ",", ".") . "</p>";
    }
}

// Tampilkan total pembayaran
echo "<h3>Total Pembayaran:</h3>";
echo "<p>Rp " . number_format($subtotal, 0, ",", ".") . "</p>";


if ($response) {
    // Decode respons JSON dari API Duitku
    $result = json_decode($response, true);

    // Periksa apakah permintaan berhasil
    if (isset($result['paymentFee']) && is_array($result['paymentFee'])) {
        // Metode pembayaran berhasil diambil, tampilkan daftar metode pembayaran
        $availablePaymentMethods = $result['paymentFee'];

        echo "<h3>Metode Pembayaran yang Tersedia:</h3>";

        if (!empty($availablePaymentMethods)) {
            foreach ($availablePaymentMethods as $paymentMethod) {
                $paymentName = $paymentMethod['paymentName'];
                $paymentImage = $paymentMethod['paymentImage'];
                $paymentMethod = $paymentMethod['paymentMethod'];
                echo "<div class='card'>";
                echo "<div class='card-content'>";
                echo "<a href='bayar.php?paymentMethod=$paymentMethod&paymentName=$paymentName&id_transaksi=$id_transaksi&subtotal=$subtotal'>";
                echo "<img src='" . $paymentImage . "' alt='" . $paymentName . "'>";
                echo "<p>" . $paymentName . "</p>";
                echo "</div>";
                echo "</div>";

            }
        } else {
            echo "Tidak ada metode pembayaran yang tersedia.";
        }
    } else {
        // Menampilkan pesan kesalahan jika permintaan gagal
        echo "Gagal mengambil daftar metode pembayaran dari API Duitku.";
    }
} else {
    echo "Gagal melakukan permintaan ke API Duitku.";
}



?>



<p><a href="list_transaksi.php">Kembali ke Daftar Transaksi</a></p>

<head>
    <style>

  .card {
        border: 1px solid #ddd;
        padding: 20px;
        margin: 10px;
        text-align: center;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0px 0px 5px #aaa;
        display: inline-block;
        width: 200px; /* Sesuaikan dengan lebar yang diinginkan */
    }

    .card img {
        max-width: 100px;
        max-height: 50px;
        margin-bottom: 10px;
    }

    .card p {
        font-size: 14px;
        color: #888;
        margin-bottom: 15px;
    }

    .card a {
        display: inline-block;
        padding: 8px 20px;
        background-color: #fff;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .card a:hover {
        background-color: #0056b3;
    }
    </style>
</head>