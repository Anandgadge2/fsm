document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.smart-sort').forEach(table => {

        const headers = table.querySelectorAll('th');
        const tbody = table.querySelector('tbody');

        if (!tbody) return;

        headers.forEach((header, colIndex) => {

            let direction = null; // desc → asc

            header.addEventListener('click', () => {

                headers.forEach(h =>
                    h.classList.remove('sort-asc', 'sort-desc')
                );

                const rows = Array.from(tbody.querySelectorAll('tr'));

                const isNumber =
                    header.dataset.type === 'number' ||
                    rows.every(row => {
                        const text = row.children[colIndex]?.innerText.trim();
                        return text !== '' && !isNaN(text);
                    });

                direction = direction === 'desc' ? 'asc' : 'desc';
                header.classList.add(
                    direction === 'asc' ? 'sort-asc' : 'sort-desc'
                );

                rows.sort((a, b) => {
                    let A = a.children[colIndex]?.innerText.trim() ?? '';
                    let B = b.children[colIndex]?.innerText.trim() ?? '';

                    if (isNumber) {
                        A = parseFloat(A) || 0;
                        B = parseFloat(B) || 0;
                        return direction === 'asc' ? A - B : B - A;
                    }

                    return direction === 'asc'
                        ? A.localeCompare(B)
                        : B.localeCompare(A);
                });

                rows.forEach(row => tbody.appendChild(row));
            });

        });

    });

});
