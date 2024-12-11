document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    form.addEventListener('submit', function () {
        const successModal = document.getElementById('feedbackSuccessModal');
        successModal.addEventListener('hidden.bs.modal', function () {
            form.reset();
        });
    });
});