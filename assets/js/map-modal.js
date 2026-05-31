let map;
let marker;

function openMapModal(lat, lng) {
    const modal = document.getElementById('mapModal');
    modal.style.display = 'flex';

    // Inisialisasi map hanya jika belum ada, kalau sudah ada set view aja
    if (!map) {
        map = L.map('map').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        marker = L.marker([lat, lng]).addTo(map);
    } else {
        map.setView([lat, lng], 14);
        marker.setLatLng([lat, lng]);
    }

    // Workaround leaflet rendering bug di dalam modal
    setTimeout(() => {
        map.invalidateSize();
    }, 100);
}

function closeMapModal() {
    document.getElementById('mapModal').style.display = 'none';
}
