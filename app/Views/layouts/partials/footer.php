<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('shown.bs.collapse', function (event) {
    const button = document.querySelector('[data-bs-target="#' + event.target.id + '"]');

    if (!button) {
        return;
    }

    const arrow = button.querySelector('.menu-arrow');

    if (arrow) {
        arrow.textContent = '▾';
    }
});

document.addEventListener('hidden.bs.collapse', function (event) {
    const button = document.querySelector('[data-bs-target="#' + event.target.id + '"]');

    if (!button) {
        return;
    }

    const arrow = button.querySelector('.menu-arrow');

    if (arrow) {
        arrow.textContent = '▸';
    }
});
</script>