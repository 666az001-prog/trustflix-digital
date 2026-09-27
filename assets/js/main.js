document.addEventListener('DOMContentLoaded', function () {
    const attachSearch = (inputId, selector) => {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('keyup', function () {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll(selector).forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    };

    attachSearch('searchClient', '.client-row');
    attachSearch('searchAccount', '.account-row');
    attachSearch('searchPayment', '.payment-row');
});
