(function () {
    var shell = document.querySelector('.admin-shell');
    var toggle = document.getElementById('sidebarToggle');
    if (shell && toggle) {
        var backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        shell.appendChild(backdrop);
        toggle.addEventListener('click', function () { shell.classList.toggle('sidebar-open'); });
        backdrop.addEventListener('click', function () { shell.classList.remove('sidebar-open'); });
    }
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) { if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault(); });
    });
    document.querySelectorAll('form[data-confirm-submit]').forEach(function (f) {
        f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm-submit'))) e.preventDefault(); });
    });
    /* Auto-slug preview on the vehicle form */
    var slugOut = document.getElementById('slugPreview');
    if (slugOut) {
        var fields = ['year', 'make', 'model', 'trim'].map(function (n) { return document.querySelector('[name="' + n + '"]'); });
        function upd() {
            var s = fields.map(function (f) { return f ? f.value : ''; }).join(' ').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            slugOut.textContent = s || '…';
        }
        fields.forEach(function (f) { if (f) f.addEventListener('input', upd); });
        upd();
    }
    /* Preview selected files before upload */
    var picker = document.getElementById('photoPicker');
    var preview = document.getElementById('photoPreview');
    if (picker && preview) {
        picker.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.prototype.forEach.call(picker.files, function (file) {
                if (!file.type.match(/^image\//)) return;
                var img = document.createElement('img');
                img.className = 'thumb me-2 mb-2';
                img.style.width = '96px'; img.style.height = '60px';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            });
        });
    }
})();
