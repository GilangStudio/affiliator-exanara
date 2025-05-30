<script>
    function showAlert(input, type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <div class="d-flex">
                    <div>
                        <i class="ti ti-alert-triangle icon alert-icon text-danger me-2"></i>
                    </div>
                    <div>${message}</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert"></a>
            `;
            
            //insert before the input element
            input.parentNode.insertBefore(alertDiv, input);
            
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
    }
</script>