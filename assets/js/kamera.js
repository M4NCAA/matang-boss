document.addEventListener('DOMContentLoaded', function() {
    const btnStartCamera = document.getElementById('btn-start-camera');
    const btnScan = document.getElementById('btn-scan');
    const videoStream = document.getElementById('camera-stream');
    const uploadedPreview = document.getElementById('uploaded-preview');
    const uploadImage = document.getElementById('upload-image');
    const captureCanvas = document.getElementById('capture-canvas');
    const cameraFallback = document.getElementById('camera-fallback');
    const scanLine = document.getElementById('scan-line');
    const scanOverlay = document.getElementById('scan-overlay');
    const scanPercent = document.getElementById('scan-percent');
    const resultCard = document.getElementById('result-card');
    const gpsStatus = document.getElementById('gps-status');
    const btnSimpan = document.getElementById('btn-simpan-hasil');

    let stream = null;
    let userLat = null;
    let userLng = null;
    let lastResult = null;

    // GPS
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                gpsStatus.innerHTML = '<i class="fas fa-map-marker-alt"></i> Lokasi Ditemukan';
                gpsStatus.className = 'badge badge-success';
                gpsStatus.style.padding = '8px 16px';
                gpsStatus.style.fontSize = '0.88rem';
            },
            () => {
                gpsStatus.innerHTML = '<i class="fas fa-location-crosshairs"></i> GPS Tidak Tersedia';
                gpsStatus.className = 'badge badge-danger';
                gpsStatus.style.padding = '8px 16px';
                gpsStatus.style.fontSize = '0.88rem';
            }
        );
    }

    // Camera toggle
    btnStartCamera.addEventListener('click', async function() {
        if (!stream) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } });
                videoStream.srcObject = stream;
                videoStream.style.display = 'block';
                cameraFallback.style.display = 'none';
                btnStartCamera.innerHTML = '<i class="fas fa-video-slash"></i> Matikan Kamera';
                btnScan.disabled = false;
            } catch (err) {
                alert("Gagal mengakses kamera: " + err.message);
            }
        } else {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
            videoStream.style.display = 'none';
            cameraFallback.style.display = 'block';
            btnStartCamera.innerHTML = '<i class="fas fa-power-off"></i> Hidupkan Kamera';
            btnScan.disabled = uploadedPreview.style.display === 'none';
        }
    });

    // File Upload
    uploadImage.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Stop camera if running
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
                videoStream.style.display = 'none';
                btnStartCamera.innerHTML = '<i class="fas fa-power-off"></i> Hidupkan Kamera';
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                uploadedPreview.src = event.target.result;
                uploadedPreview.style.display = 'block';
                cameraFallback.style.display = 'none';
                btnScan.disabled = false;
            };
            reader.readAsDataURL(file);
        }
    });

    // Scan process (mock AI — replace with real fetch to Python endpoint later)
    btnScan.addEventListener('click', function() {
        if (!stream && uploadedPreview.style.display === 'none') return;

        btnScan.disabled = true;
        btnStartCamera.disabled = true;
        uploadImage.disabled = true;
        scanLine.style.display = 'block';
        scanOverlay.style.display = 'flex';
        resultCard.style.display = 'none';

        if (stream) {
            // Capture frame to canvas from video
            captureCanvas.width = videoStream.videoWidth;
            captureCanvas.height = videoStream.videoHeight;
            captureCanvas.getContext('2d').drawImage(videoStream, 0, 0);
        } else {
            // Draw uploaded image to canvas
            captureCanvas.width = uploadedPreview.naturalWidth;
            captureCanvas.height = uploadedPreview.naturalHeight;
            captureCanvas.getContext('2d').drawImage(uploadedPreview, 0, 0);
        }

        let percent = 0;
        const interval = setInterval(() => {
            percent += Math.floor(Math.random() * 12) + 5;
            if (percent > 100) percent = 100;
            scanPercent.innerText = percent + '%';
            if (percent >= 100) {
                clearInterval(interval);
                setTimeout(showMockResults, 600);
            }
        }, 180);
    });

    function showMockResults() {
        scanLine.style.display = 'none';
        scanOverlay.style.display = 'none';
        btnScan.disabled = false;
        btnStartCamera.disabled = false;
        uploadImage.disabled = false;

        // Mock result — replace with actual AI response
        const results = [
            { kematangan: 'Matang', confidence: 97.5, penyakit: null, solusi: 'Buah sudah siap panen. Segera lakukan pemanenan dan angkut ke RAMP terdekat dalam waktu 24 jam untuk menjaga kualitas.' },
            { kematangan: 'Mentah', confidence: 93.2, penyakit: null, solusi: 'Buah belum siap panen. Tunggu 2-3 minggu lagi. Pastikan nutrisi tanah tercukupi untuk optimalisasi kematangan.' },
            { kematangan: 'Lewat Matang', confidence: 88.4, penyakit: 'Ganoderma', solusi: 'Terdeteksi infeksi Ganoderma. Segera isolasi pohon yang terinfeksi. Lakukan aplikasi fungisida berbahan aktif trifloxystrobin. Hubungi PPL terdekat.' }
        ];

        const r = results[Math.floor(Math.random() * results.length)];
        lastResult = r;

        document.getElementById('res-kematangan').textContent = r.kematangan;
        document.getElementById('res-confidence').innerHTML = '<i class="fas fa-check-circle"></i> ' + r.confidence + '% Akurasi CNN';

        const penyakitEl = document.getElementById('res-penyakit');
        if (r.penyakit) {
            penyakitEl.innerHTML = '<span style="color: #DC2626;"><i class="fas fa-virus"></i> ' + r.penyakit + '</span>';
        } else {
            penyakitEl.innerHTML = '<span style="color: #059669;"><i class="fas fa-heart"></i> Sehat (Normal)</span>';
        }

        document.getElementById('res-solusi').textContent = r.solusi;
        resultCard.style.display = 'block';
        setTimeout(() => resultCard.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
    }

    // Save to database
    if (btnSimpan) {
        btnSimpan.addEventListener('click', function() {
            if (!lastResult) return;

            const imageData = captureCanvas.toDataURL('image/jpeg', 0.7);
            const formData = new FormData();
            formData.append('gambar_base64', imageData);
            formData.append('hasil_kematangan', lastResult.kematangan);
            formData.append('confidence_score', lastResult.confidence);
            formData.append('nama_penyakit', lastResult.penyakit || '');
            formData.append('solusi_penyakit', lastResult.solusi || '');
            formData.append('latitude', userLat || 0);
            formData.append('longitude', userLng || 0);

            btnSimpan.disabled = true;
            btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            fetch('api/simpan_scan.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        btnSimpan.innerHTML = '<i class="fas fa-check"></i> Tersimpan!';
                        btnSimpan.style.background = '#059669';
                    } else {
                        btnSimpan.innerHTML = '<i class="fas fa-times"></i> Gagal';
                        btnSimpan.disabled = false;
                        alert(data.message);
                    }
                })
                .catch(() => {
                    btnSimpan.innerHTML = '<i class="fas fa-save"></i> Simpan ke Riwayat';
                    btnSimpan.disabled = false;
                    alert('Koneksi ke server gagal.');
                });
        });
    }
});
