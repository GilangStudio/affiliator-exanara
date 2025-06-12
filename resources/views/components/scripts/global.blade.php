<script>
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-global');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    const inputs = document.querySelectorAll('input[type="tel"]');
    inputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    const form = document.querySelector('form');
    const submitBtn = document.querySelector('form button[type="submit"]');

    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        form.classList.add('loading');
    });
</script>