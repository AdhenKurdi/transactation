<!DOCTYPE html>
<html>
<head>
    <title>Tambah Transaksi</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f2f2;
        margin: 0;
        padding: 0;
    }

    h2 {
        background-color: #007bff;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
    }

    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 5px #aaa;
        width: 300px; /* Sesuaikan dengan lebar yang diinginkan */
        margin: 0 auto;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    select, input[type="number"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    input[type="submit"] {
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

    input[type="submit"]:hover {
        background-color: #0056b3;
    }
</style>

</head>
<body>
    <h2>Tambah Transaksi</h2>
    <form action="tambah_transaksi.php" method="POST">
        <label for="id_barang">Pilih Barang:</label>
        <select name="id_barang">
            <?php
            include("koneksi.php");

            $sql_barang = "SELECT id_barang, nama_barang FROM barang";
            $result_barang = mysqli_query($conn, $sql_barang);

            if (mysqli_num_rows($result_barang) > 0) {
                while ($row_barang = mysqli_fetch_assoc($result_barang)) {
                    echo "<option value='" . $row_barang["id_barang"] . "'>" . $row_barang["nama_barang"] . "</option>";
                }
            }

            mysqli_close($conn);
            ?>
        </select><br>

        <label for="id_user">Pembeli:</label>
        <select name="id_user">
            <?php
            include("koneksi.php");

            $sql_users = "SELECT id, nama_user FROM users";
            $result_users = mysqli_query($conn, $sql_users);

            if (mysqli_num_rows($result_users) > 0) {
                while ($row_users = mysqli_fetch_assoc($result_users)) {
                    echo "<option value='" . $row_users["id"] . "'>" . $row_users["nama_user"] . "</option>";
                }
            }

            mysqli_close($conn);
            ?>
        </select><br>

        Jumlah: <input type="number" id="jumlah" name="jumlah" required><br>
        <input type="submit" value="Simpan">
    </form>
</body>
</html>
