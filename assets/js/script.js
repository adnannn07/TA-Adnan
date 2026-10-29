// Dynamic sub kategori via AJAX (dipakai di halaman yang punya select#kategori & select#sub)
document.addEventListener('DOMContentLoaded', function () {
    var katSelect = document.getElementById('kategori');
    var subSelect = document.getElementById('sub');
    if (!katSelect || !subSelect) return;

    katSelect.addEventListener('change', function () {
        var kat_id = this.value;
        // get_sub.php ada di root, jadi path relatif perlu disesuaikan tergantung folder halaman saat ini
        var base = window.location.pathname.includes('/admin/') ? '../' : '';
        subSelect.innerHTML = '<option value="">Memuat...</option>';
        if (kat_id) {
            fetch(base + 'get_sub.php?kategori_id=' + kat_id)
                .then(response => response.json())
                .then(data => {
                    subSelect.innerHTML = '<option value="">-- Pilih Sub --</option>';
                    data.forEach(function (item) {
                        subSelect.innerHTML += `<option value="${item.id}">${item.nama}</option>`;
                    });
                });
        } else {
            subSelect.innerHTML = '<option value="">-- Pilih kategori dulu --</option>';
        }
    });
});
