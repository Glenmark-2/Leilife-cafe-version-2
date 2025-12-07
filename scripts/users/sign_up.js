const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
document.getElementById('openTerms').addEventListener('click', function(e) {
        e.preventDefault();
        modal.show();
});