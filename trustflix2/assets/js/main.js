document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchClient');

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach((row) => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
