document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.list__button--click').forEach(button => {
        button.addEventListener('click', () => {
            const menu = button.nextElementSibling;
            const height = menu.clientHeight === 0 ? menu.scrollHeight : 0;
            menu.style.height = `${height}px`;
        });
    });
});

function filtrarTabla() {
    const filter = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#tbodyPostulantes tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}

function filtrarStatus() {
    const filter = document.getElementById('statusSearch').value.toLowerCase();
    document.querySelectorAll('#tbodyStatus tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}