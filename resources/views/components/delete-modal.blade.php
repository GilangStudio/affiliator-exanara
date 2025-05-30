{{-- Global Delete Confirmation Modal --}}
<div class="modal modal-blur fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <form class="modal-content" id="delete-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle icon mb-2 text-danger icon-lg"></i>
                <h3>Are you sure?</h3>
                <div class="text-secondary" id="delete-message">
                    Do you really want to delete this item? This process cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn w-100" data-bs-dismiss="modal">Cancel</button>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-danger w-100">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Handle Delete Button Click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
                e.preventDefault();
                
                const button = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
                const itemName = button.getAttribute('data-name');
                const deleteUrl = button.getAttribute('data-url');
                
                // Update modal content
                const deleteForm = document.getElementById('delete-form');
                const deleteMessage = document.getElementById('delete-message');
                
                deleteForm.action = deleteUrl;
                deleteMessage.innerHTML = `Do you really want to delete "<strong>${itemName}</strong>"? This process cannot be undone.`;
                
                // Show modal
                const deleteModal = new bootstrap.Modal(document.getElementById('delete-modal'));
                deleteModal.show();
            }
        });
    });
</script>