<div class="col-12">
    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-global w-100" role="alert">
        <div class="d-flex">
            <div>
                <i class="ti ti-check me-2"></i>
            </div>
            <div>{{ session('success') }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible alert-global w-100" role="alert">
        <div class="d-flex">
            <div>
                <i class="ti ti-exclamation-circle me-2"></i>
            </div>
            <div>{{ session('error') }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible alert-global w-100" role="alert">
        <div class="d-flex">
            <div>
                <i class="ti ti-info me-2"></i>
            </div>
            <div>{{ session('info') }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible alert-global w-100" role="alert">
        <div class="d-flex">
            <div>
                <i class="ti ti-exclamation-circle me-2"></i>
            </div>
            <div>{{ $errors->first() }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    @endif
</div>


{{-- <script>
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
</script> --}}