function getLocation() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        }, function (error) {
            document.getElementById('latitude').value = 23.8103;
            document.getElementById('longitude').value = 90.4125;
        });
    } else {
        document.getElementById('latitude').value = 23.8103;
        document.getElementById('longitude').value = 90.4125;
    }
}

getLocation();

(function () {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
})();