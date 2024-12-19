document.getElementById('search-users').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const searchValue = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('search', searchValue);
        currentUrl.searchParams.set('page', '1');
        window.location.href = currentUrl.toString();
    }
});