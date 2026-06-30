<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scan Wajah - ScanWajah</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 500px; margin: 30px auto; padding: 0 15px; text-align: center; background:#f4f6f8; }
    .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    h2 { color:#1e293b; }
    video, canvas { width: 100%; border-radius: 8px; border: 2px solid #333; }
    button { padding: 12px; width: 100%; border: none; border-radius: 6px; margin-top: 10px; cursor: pointer; font-size:15px; }
    #btnScan { background: #16a34a; color: white; }
    #btnRegister { background:#2563eb; color:white; }
    #result { margin-top: 15px; padding: 15px; border-radius: 8px; text-align:left; }
    .success { background: #dcfce7; color: #166534; }
    .error { background: #fee2e2; color: #991b1b; }
    .loading { background: #fef9c3; color: #854d0e; }
</style>
</head>
<body>
<div class="card">
<h2>🎥 Scan Absensi Wajah</h2>

<video id="video" autoplay playsinline></video>
<canvas id="canvas" style="display:none;"></canvas>

<button id="btnScan">🔍 Scan Wajah Sekarang</button>
<a href="register.php"><button type="button" id="btnRegister">+ Belum Terdaftar? Daftar Dulu</button></a>

<div id="result"></div>
</div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const resultDiv = document.getElementById('result');

navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
    .then(stream => { video.srcObject = stream; })
    .catch(err => {
        resultDiv.innerHTML = "Tidak bisa akses kamera: " + err;
        resultDiv.className = "error";
    });

document.getElementById('btnScan').addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL('image/jpeg');

    resultDiv.innerHTML = "⏳ Memproses, mohon tunggu...";
    resultDiv.className = "loading";

    fetch('process_scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: dataUrl })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            resultDiv.className = "success";
            resultDiv.innerHTML = `
                <h3>✅ Wajah Dikenali</h3>
                <p><b>Nama:</b> ${data.nama}</p>
                <p><b>NIM:</b> ${data.nim}</p>
                <p><b>Jurusan:</b> ${data.jurusan}</p>
                <p><b>Umur:</b> ${data.umur}</p>
                <p><b>Akurasi:</b> ${(data.confidence * 100).toFixed(1)}%</p>
            `;
        } else {
            resultDiv.className = "error";
            resultDiv.innerHTML = `❌ ${data.message}`;
        }
    })
    .catch(err => {
        resultDiv.className = "error";
        resultDiv.innerHTML = "Terjadi kesalahan: " + err;
    });
});
</script>
</body>
</html>
