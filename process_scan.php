<?php
require 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$imageBase64 = $input['image'] ?? '';

if (empty($imageBase64)) {
    echo json_encode(["status" => "error", "message" => "Tidak ada gambar dikirim"]);
    exit;
}

// Simpan foto scan sementara
$imgData = preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64);
$imgData = base64_decode($imgData);

if (!is_dir(__DIR__ . "/scan_temp")) {
    mkdir(__DIR__ . "/scan_temp", 0755, true);
}

$tempFile = __DIR__ . "/scan_temp/scan_" . time() . "_" . uniqid() . ".jpg";
file_put_contents($tempFile, $imgData);

// Panggil Python untuk pengenalan wajah
$pythonPath = "python"; // di Windows biasanya "python", bukan "python3"
$scriptPath = __DIR__ . "/python/recognize.py";
$uploadsDir = __DIR__ . "/uploads";

$cmd = escapeshellcmd("$pythonPath \"$scriptPath\" \"$tempFile\" \"$uploadsDir\"");
$output = shell_exec($cmd . " 2>&1");

@unlink($tempFile); // hapus file temp setelah diproses

$result = json_decode($output, true);

if (!$result) {
    echo json_encode(["status" => "error", "message" => "Gagal memproses Python. Detail: " . $output]);
    exit;
}

if ($result['status'] === 'success') {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE foto = ?");
    $stmt->execute([$result['filename']]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mhs) {
        echo json_encode([
            "status" => "success",
            "nama" => $mhs['nama'],
            "nim" => $mhs['nim'],
            "jurusan" => $mhs['jurusan'],
            "umur" => $mhs['umur'],
            "confidence" => $result['confidence']
        ]);
    } else {
        echo json_encode(["status" => "not_found", "message" => "Data wajah ditemukan tapi tidak ada di database"]);
    }
} else {
    echo json_encode($result);
}
