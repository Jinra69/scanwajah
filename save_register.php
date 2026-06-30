<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $umur = (int)$_POST['umur'];
    $jurusan = trim($_POST['jurusan']);
    $nim = trim($_POST['nim']);
    $fotoBase64 = $_POST['foto_base64'] ?? '';

    if (empty($nama) || empty($jurusan) || empty($nim) || $umur <= 0) {
        die("Error: Semua field wajib diisi dengan benar. <a href='register.php'>Kembali</a>");
    }

    if (empty($fotoBase64)) {
        die("Error: Foto wajib diisi! <a href='register.php'>Kembali</a>");
    }

    // Decode base64 jadi file gambar
    $imgData = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
    $imgData = base64_decode($imgData);

    $safeName = preg_replace('/[^A-Za-z0-9_]/', '_', $nama);
    $filename = $nim . "_" . $safeName . ".jpg";
    $filepath = __DIR__ . "/uploads/" . $filename;

    if (!is_dir(__DIR__ . "/uploads")) {
        mkdir(__DIR__ . "/uploads", 0755, true);
    }

    file_put_contents($filepath, $imgData);

    // Validasi: pastikan ada wajah terdeteksi di foto (panggil python)
    $pythonPath = "python"; // di Windows biasanya "python", bukan "python3"
    $scriptPath = __DIR__ . "/python/check_face.py";
    $cmd = escapeshellcmd("$pythonPath \"$scriptPath\" \"$filepath\"");
    $output = shell_exec($cmd . " 2>&1");
    $result = json_decode($output, true);

    if (!$result || $result['has_face'] !== true) {
        unlink($filepath); // hapus foto kalau ga ada wajah
        die("Error: Wajah tidak terdeteksi pada foto. Pastikan wajah terlihat jelas dan pencahayaan cukup.<br>
             Detail: " . htmlspecialchars($output) . "<br>
             <a href='register.php'>Coba lagi</a>");
    }

    // Simpan ke database
    try {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, umur, jurusan, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nim, $nama, $umur, $jurusan, $filename]);
        echo "✅ Registrasi berhasil!<br><br><a href='index.php'>Lanjut ke halaman Scan</a> | <a href='register.php'>Daftar Lagi</a>";
    } catch (PDOException $e) {
        unlink($filepath);
        die("Gagal simpan data (NIM mungkin sudah terdaftar): " . $e->getMessage() . "<br><a href='register.php'>Kembali</a>");
    }
} else {
    header("Location: register.php");
    exit;
}
