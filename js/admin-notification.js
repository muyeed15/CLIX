document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('create-notification-form');
    const responseDiv = document.createElement('div');
    responseDiv.className = 'alert mt-3';
    responseDiv.style.display = 'none';
    form.appendChild(responseDiv);

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('type', document.getElementById('notification-type').value);
        formData.append('title', document.getElementById('notification-header').value);
        formData.append('message', document.getElementById('notification-message').value);

        try {
            const response = await fetch('admin-notification.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            responseDiv.style.display = 'block';
            responseDiv.innerHTML = result.message;

            if (result.success) {
                responseDiv.className = 'alert alert-success mt-3';
                form.reset();
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                responseDiv.className = 'alert alert-danger mt-3';
            }
        } catch (error) {
            responseDiv.style.display = 'block';
            responseDiv.className = 'alert alert-danger mt-3';
            responseDiv.innerHTML = 'An error occurred while creating the notification.';
        }
    });

    const searchInput = document.getElementById('search-notifications');

    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchValue = this.value.trim();
            if (searchValue !== '') {
                window.location.href = `?page=1&search=${encodeURIComponent(searchValue)}`;
            } else {
                window.location.href = '?page=1';
            }
        }
    });

    document.querySelectorAll('.pagination .page-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            window.location.href = href;
            setTimeout(() => {
                const table = document.getElementById('notifications-table');
                if (table) {
                    table.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }, 100);
        });
    });

    if (new URLSearchParams(window.location.search).has('page')) {
        const table = document.getElementById('notifications-table');
        if (table) {
            table.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});