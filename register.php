<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Wajah - ScanWajah</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 500px; margin: 30px auto; padding: 0 15px; background:#f4f6f8; }
    .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    h2 { text-align:center; color:#1e293b; }
    label { font-weight:bold; font-size:14px; color:#334155; display:block; margin-top:12px; }
    input { width: 100%; padding: 10px; margin-top:4px; box-sizing: border-box; border:1px solid #cbd5e1; border-radius:6px; }
    video, canvas, img#preview { width: 100%; border-radius: 8px; border: 2px solid #333; margin-top:10px; }
    button { padding: 12px; width: 100%; border: none; border-radius: 6px; cursor: pointer; font-size:15px; margin-top:10px; }
    #btnCapture { background:#2563eb; color:white; }
    #btnSubmit { background:#16a34a; color:white; }
    #preview { display:none; }
    a.link { display:block; text-align:center; margin-top:15px; color:#2563eb; }
</style>
</head>
<body>
<div class="card">
<h2>📝 Form Registrasi</h2>

<form id="formRegister" action="save_register.php" method="POST">
    <label>Nama</label>
    <input type="text" name="nama" required>

    <label>Umur</label>
    <input type="number" name="umur" required>

    <label>Jurusan</label>
    <input type="text" name="jurusan" required>

    <label>NIM</label>
    <input type="text" name="nim" required>

    <label>Foto Wajah</label>
    <video id="video" autoplay playsinline></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <img id="preview">

    <button type="button" id="btnCapture">📸 Ambil Foto</button>
    <input type="hidden" name="foto_base64" id="fotoBase64">

    <button type="submit" id="btnSubmit">Simpan Data</button>
</form>
<a class="link" href="index.php">← Kembali ke halaman Scan</a>
</div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const btnCapture = document.getElementById('btnCapture');
const fotoBase64 = document.getElementById('fotoBase64');

navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
    .then(stream => { video.srcObject = stream; })
    .catch(err => alert("Tidak bisa akses kamera: " + err));

btnCapture.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    const dataUrl = canvas.toDataURL('image/jpeg');
    fotoBase64.value = dataUrl;

    preview.src = dataUrl;
    preview.style.display = 'block';
    video.style.display = 'none';
    btnCapture.textContent = "🔄 Ambil Ulang Foto";
});

document.getElementById('formRegister').addEventListener('submit', function (e) {
    if (!fotoBase64.value) {
        e.preventDefault();
        alert("Silakan ambil foto terlebih dahulu!");
    }
});
</script>
</body>
</html>
