<script>
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; width: 350px;';
        document.body.appendChild(container);
        return container;
    }

    function showToast(message, type) {
        const toastContainer = createToastContainer();
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.style.cssText = 'margin-bottom: 0.5rem;';
        toast.innerHTML = `
            <div class="d-flex">
                <div>
                    <i class="ti ti-${type === 'success' ? 'check' : 'exclamation-circle'} icon alert-icon me-2"></i>
                </div>
                <div>${message}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
</script>