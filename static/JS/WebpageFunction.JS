
window.onload = function() {

    const urlParams = new URLSearchParams(window.location.search);

    const message = urlParams.get('message');
    const type = urlParams.get('type');

    if (message && type) {
        const messageDiv = document.getElementById('message');
        messageDiv.textContent = decodeURIComponent(message); 
        messageDiv.classList.add(type);
    }
};

document.getElementById('resetForm').addEventListener('submit', function(e) {
    document.querySelector('button[type="submit"]').disabled = true;
});