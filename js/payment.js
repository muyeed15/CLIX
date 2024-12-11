document.addEventListener('DOMContentLoaded', function () {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const cardPaymentSection = document.getElementById('card-payment-section');
    const cardInputs = cardPaymentSection.querySelectorAll('input');
    let selectedMobilePayment = null;

    function resetMobilePayments() {
        paymentOptions.forEach(option => {
            option.classList.remove('selected');
        });
    }

    function toggleCardPaymentSection(disable) {
        if (disable) {
            cardPaymentSection.classList.add('disabled');
            cardInputs.forEach(input => {
                input.disabled = true;
            });
        } else {
            cardPaymentSection.classList.remove('disabled');
            cardInputs.forEach(input => {
                input.disabled = false;
            });
        }
    }

    paymentOptions.forEach(option => {
        option.addEventListener('click', function () {
            const method = this.getAttribute('data-method');

            if (selectedMobilePayment === method) {
                // Deselect current mobile payment
                resetMobilePayments();
                toggleCardPaymentSection(false);
                selectedMobilePayment = null;
            } else {
                // Select new mobile payment
                resetMobilePayments();
                this.classList.add('selected');
                toggleCardPaymentSection(true);
                selectedMobilePayment = method;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    if (typeof paymentStatus !== 'undefined') {
        if (paymentStatus === 'success') {
            const successModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));

            document.getElementById('paymentSuccessModal').addEventListener('hidden.bs.modal', () => {
                window.location.href = 'dashboard.php';
            });

            successModal.show();
        } else if (paymentStatus === 'error') {
            const errorModal = new bootstrap.Modal(document.getElementById('paymentErrorModal'));
            document.getElementById('errorMessage').textContent = errorMessage || "An unexpected error occurred.";

            document.getElementById('paymentErrorModal').addEventListener('hidden.bs.modal', () => {
                window.location.href = 'dashboard.php';
            });

            errorModal.show();
        }
    }
});