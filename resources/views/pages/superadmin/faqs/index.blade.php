@extends('layouts.main')

@section('title', 'Kelola FAQ')

@section('content')

@include('components.alert')

<!-- Header & Actions -->
<div class="row">
    <div class="col-md-8 mb-2">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-faq">
                <i class="ti ti-plus me-1"></i>
                Tambah FAQ
            </button>
            <button type="button" class="btn btn-outline-secondary" id="toggle-reorder">
                <i class="ti ti-arrows-sort me-1"></i>
                <span class="reorder-text">Atur Urutan</span>
            </button>
        </div>
    </div>
    <div class="col-md-4">
        <div class="d-flex gap-2">
            <!-- Search -->
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" id="search-input" placeholder="Cari FAQ...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-2 btn-icon" id="search-btn">
                        <i class="ti ti-search icon icon-2"></i>
                    </button>
                </div>
                <div class="col">
                    <!-- Category Filter -->
                    <select class="form-select category-filter" id="category-filter">
                        <option value="">Semua Kategori</option>
                        <option value="general">Umum</option>
                        <option value="project">Project</option>
                        <option value="payment">Pembayaran</option>
                        <option value="technical">Teknis</option>
                        <option value="commission">Komisi</option>
                        <option value="account">Akun</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row gy-2">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total FAQ</div>
                </div>
                <div class="h1 mb-0">{{ $faqs->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">FAQ Aktif</div>
                </div>
                <div class="h1 mb-0 text-green">{{ $faqs->where('is_active', true)->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">FAQ Tidak Aktif</div>
                </div>
                <div class="h1 mb-0 text-red">{{ $faqs->where('is_active', false)->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Kategori</div>
                </div>
                <div class="h1 mb-0 text-purple">{{ $faqs->pluck('category')->unique()->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div id="order-instructions"></div>
    </div>
</div>

<!-- Main Table -->
<div class="col-12">
    <div class="card p-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-vcenter" id="faq-table">
                    <thead>
                        <tr>
                            <th width="50" class="reorder-handle-header" style="display: none;">
                                <i class="ti ti-arrows-sort"></i>
                            </th>
                            <th width="50">No</th>
                            <th>Pertanyaan & Jawaban</th>
                            <th width="120">Kategori</th>
                            <th width="80">Urutan</th>
                            <th width="80">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-table">
                        @forelse($faqs as $index => $faq)
                        <tr data-id="{{ $faq->id }}" class="sortable-row">
                            <td class="reorder-handle" style="display: none;">
                                <div class="cursor-move text-secondary">
                                    <i class="ti ti-grip-vertical"></i>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold mb-1">{{ $faq->question }}</div>
                                <div class="text-secondary small faq-answer" data-full="{{ $faq->answer }}">
                                    {{ Str::limit(strip_tags($faq->answer), 150) }}
                                    @if(strlen(strip_tags($faq->answer)) > 150)
                                        <a href="#" class="text-primary expand-answer ms-1">Lihat selengkapnya</a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $faq->category_badge_color }}-lt">
                                    {{ $faq->category_label }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $faq->sort_order }}</td>
                            <td>
                                <span class="badge bg-{{ $faq->is_active ? 'success' : 'danger' }}-lt">
                                    {{ $faq->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap action-buttons">
                                    <button type="button" class="btn btn-icon bg-light" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#view-faq"
                                            data-id="{{ $faq->id }}"
                                            data-question="{{ $faq->question }}"
                                            data-answer="{{ $faq->answer }}"
                                            data-category="{{ $faq->category }}"
                                            title="Lihat Detail">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-icon bg-light" 
                                                data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#edit-faq"
                                                    data-id="{{ $faq->id }}"
                                                    data-question="{{ $faq->question }}"
                                                    data-answer="{{ $faq->answer }}"
                                                    data-category="{{ $faq->category }}"
                                                    data-status="{{ $faq->is_active }}">
                                                <i class="ti ti-edit me-2"></i>
                                                Edit
                                            </button>
                                            
                                            <form action="{{ route('superadmin.faqs.toggle-status', $faq) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ti ti-{{ $faq->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                    {{ $faq->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <button type="button" class="dropdown-item text-danger delete-btn"
                                                    data-id="{{ $faq->id }}"
                                                    data-name="{{ $faq->question }}"
                                                    data-url="{{ route('superadmin.faqs.destroy', $faq) }}">
                                                <i class="ti ti-trash me-2"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state">
                            <td colspan="7" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="ti ti-help-circle icon icon-xl text-secondary"></i>
                                    </div>
                                    <p class="empty-title h3">Belum ada FAQ</p>
                                    <p class="empty-subtitle text-secondary">
                                        Tambahkan FAQ pertama untuk membantu user
                                    </p>
                                    <div class="empty-action">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-faq">
                                            <i class="ti ti-plus me-1"></i>
                                            Tambah FAQ Pertama
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Add FAQ --}}
<div class="modal modal-blur fade" id="add-faq" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form class="modal-content" action="{{ route('superadmin.faqs.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah FAQ Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('question') is-invalid @enderror" 
                                   name="question" value="{{ old('question') }}" required 
                                   placeholder="Masukkan pertanyaan..." />
                            @error('question')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>Umum</option>
                                <option value="project" {{ old('category') == 'project' ? 'selected' : '' }}>Project</option>
                                <option value="payment" {{ old('category') == 'payment' ? 'selected' : '' }}>Pembayaran</option>
                                <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Teknis</option>
                                <option value="commission" {{ old('category') == 'commission' ? 'selected' : '' }}>Komisi</option>
                                <option value="account" {{ old('category') == 'account' ? 'selected' : '' }}>Akun</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('answer') is-invalid @enderror" 
                                      name="answer" rows="6" required 
                                      placeholder="Berikan jawaban yang jelas dan lengkap...">{{ old('answer') }}</textarea>
                            @error('answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Berikan jawaban yang jelas dan mudah dipahami</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" 
                                   {{ old('status', true) ? 'checked' : '' }}>
                            <span class="form-check-label">FAQ Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan FAQ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit FAQ --}}
<div class="modal modal-blur fade" id="edit-faq" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form class="modal-content" id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-question" name="question" required 
                                   placeholder="Masukkan pertanyaan..." />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-category" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="general">Umum</option>
                                <option value="project">Project</option>
                                <option value="payment">Pembayaran</option>
                                <option value="technical">Teknis</option>
                                <option value="commission">Komisi</option>
                                <option value="account">Akun</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit-answer" name="answer" rows="6" required
                                      placeholder="Berikan jawaban yang jelas dan lengkap..."></textarea>
                            <small class="form-hint">Berikan jawaban yang jelas dan mudah dipahami</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit-status" name="status" value="1">
                            <span class="form-check-label">FAQ Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i>
                    Perbarui FAQ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal View FAQ --}}
<div class="modal modal-blur fade" id="view-faq" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Pertanyaan:</label>
                            <div class="card">
                                <div class="card-body" id="view-question"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Kategori:</label>
                            <div id="view-category-badge"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Jawaban:</label>
                            <div class="card">
                                <div class="card-body" id="view-answer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include Global Delete Modal --}}
@include('components.delete-modal')

@endsection

@push('scripts')
@include('components.toast')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let sortable = null;
    let isReorderMode = false;
    
    // Toggle Reorder Mode
    const toggleReorderBtn = document.getElementById('toggle-reorder');
    const table = document.querySelector('#faq-table');
    const reorderHandles = document.querySelectorAll('.reorder-handle');
    const reorderHeader = document.querySelector('.reorder-handle-header');
    const reorderText = document.querySelector('.reorder-text');

    toggleReorderBtn.addEventListener('click', function() {
        isReorderMode = !isReorderMode;
        
        if (isReorderMode) {
            enableReorderMode();
        } else {
            disableReorderMode();
        }
    });

    function enableReorderMode() {
        // Show reorder handles
        reorderHandles.forEach(handle => handle.style.display = 'table-cell');
        reorderHeader.style.display = 'table-cell';
        
        // Add reorder mode class
        table.classList.add('reorder-mode', 'reorder-active');
        
        // Update button
        toggleReorderBtn.classList.remove('btn-outline-secondary');
        toggleReorderBtn.classList.add('btn-warning');
        toggleReorderBtn.querySelector('i').className = 'ti ti-x me-1';
        reorderText.textContent = 'Selesai';
        
        // Initialize sortable
        const sortableTable = document.getElementById('sortable-table');
        sortable = new Sortable(sortableTable, {
            handle: '.reorder-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateOrder();
            }
        });
        
        // Show reorder instructions
        showReorderInstructions();
    }

    function disableReorderMode() {
        // Hide reorder handles
        reorderHandles.forEach(handle => handle.style.display = 'none');
        reorderHeader.style.display = 'none';
        
        // Remove reorder mode class
        table.classList.remove('reorder-mode', 'reorder-active');
        
        // Update button
        toggleReorderBtn.classList.remove('btn-warning');
        toggleReorderBtn.classList.add('btn-outline-secondary');
        toggleReorderBtn.querySelector('i').className = 'ti ti-arrows-sort me-1';
        reorderText.textContent = 'Atur Urutan';
        
        // Destroy sortable
        if (sortable) {
            sortable.destroy();
            sortable = null;
        }
        
        // Hide any instructions
        hideReorderInstructions();
    }

    function updateOrder() {
        const rows = document.querySelectorAll('.sortable-row');
        const orderData = [];
        
        rows.forEach((row, index) => {
            const id = row.getAttribute('data-id');
            orderData.push({
                id: id,
                order: index + 1
            });
            
            // Update nomor urut di tabel secara real-time
            const noCell = row.children[1]; // Kolom No
            const orderCell = row.children[4]; // Kolom Urutan
            
            if (noCell) noCell.textContent = index + 1;
            if (orderCell) orderCell.textContent = index + 1;
        });
        
        // Send AJAX request to update order
        fetch('{{ route('superadmin.faqs.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                orders: orderData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Urutan berhasil diperbarui', 'success');
            } else {
                showToast('Gagal memperbarui urutan', 'error');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal memperbarui urutan', 'error');
            setTimeout(() => location.reload(), 1000);
        });
    }

    function showReorderInstructions() {
        const instruction = document.createElement('div');
        instruction.id = 'reorder-instruction';
        instruction.className = 'alert alert-info alert-dismissible mt-3 mb-0';
        instruction.innerHTML = `
            <div class="d-flex">
                <div>
                    <i class="ti ti-info-circle me-2"></i>
                </div>
                <div>
                    <strong>Mode Pengaturan Urutan Aktif</strong><br>
                    Seret handle <i class="ti ti-grip-vertical"></i> untuk mengatur ulang urutan FAQ.
                </div>
            </div>
        `;
        
        const headerRow = document.querySelector('#order-instructions');
        headerRow.appendChild(instruction);
    }

    function hideReorderInstructions() {
        const instruction = document.getElementById('reorder-instruction');
        if (instruction) {
            instruction.remove();
        }
    }

    // Handle Edit Modal
    const editModal = document.getElementById('edit-faq');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const question = button.getAttribute('data-question');
        const answer = button.getAttribute('data-answer');
        const category = button.getAttribute('data-category');
        const status = button.getAttribute('data-status') === '1';

        // Update form action
        const form = document.getElementById('edit-form');
        form.action = `{{ route('superadmin.faqs.index') }}/${id}`;

        // Fill form fields
        document.getElementById('edit-question').value = question;
        document.getElementById('edit-answer').value = answer;
        document.getElementById('edit-category').value = category;
        document.getElementById('edit-status').checked = status;
    });

    // Handle View Modal
    const viewModal = document.getElementById('view-faq');
    viewModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const question = button.getAttribute('data-question');
        const answer = button.getAttribute('data-answer');
        const category = button.getAttribute('data-category');

        // Fill view fields
        document.getElementById('view-question').textContent = question;
        document.getElementById('view-answer').innerHTML = answer.replace(/\n/g, '<br>');
        
        // Set category badge
        const categoryColors = {
            'general': 'blue',
            'project': 'green', 
            'payment': 'yellow',
            'technical': 'red',
            'commission': 'purple',
            'account': 'orange',
            'other': 'gray'
        };
        const categoryLabels = {
            'general': 'Umum',
            'project': 'Project',
            'payment': 'Pembayaran',
            'technical': 'Teknis',
            'commission': 'Komisi',
            'account': 'Akun',
            'other': 'Lainnya'
        };
        const color = categoryColors[category] || 'gray';
        const label = categoryLabels[category] || 'Lainnya';
        document.getElementById('view-category-badge').innerHTML = 
            `<span class="badge bg-${color}-lt">${label}</span>`;
    });

    // Reset Add Modal when opened
    const addModal = document.getElementById('add-faq');
    addModal.addEventListener('show.bs.modal', function (event) {
        const form = addModal.querySelector('form');
        form.reset();
        form.querySelector('input[name="status"]').checked = true;
    });

    // Expand/Collapse Answer
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('expand-answer')) {
            e.preventDefault();
            const answerDiv = e.target.parentElement;
            const fullText = answerDiv.getAttribute('data-full');
            
            if (answerDiv.classList.contains('expanded')) {
                // Collapse
                answerDiv.innerHTML = fullText.substring(0, 150) + '... <a href="#" class="text-primary expand-answer ms-1">Lihat selengkapnya</a>';
                answerDiv.classList.remove('expanded');
            } else {
                // Expand
                answerDiv.innerHTML = fullText + ' <a href="#" class="text-primary expand-answer ms-1">Lihat lebih sedikit</a>';
                answerDiv.classList.add('expanded');
            }
        }
    });

    // Search functionality
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const categoryFilter = document.getElementById('category-filter');

    function performSearch() {
        const query = searchInput.value;
        const category = categoryFilter.value;
        
        // Simple client-side filtering for now
        const rows = document.querySelectorAll('.sortable-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const questionText = row.querySelector('.fw-bold').textContent.toLowerCase();
            const answerText = row.querySelector('.faq-answer').textContent.toLowerCase();
            const rowCategory = row.querySelector('.badge').textContent.toLowerCase();
            
            const matchesSearch = !query || 
                questionText.includes(query.toLowerCase()) || 
                answerText.includes(query.toLowerCase());
            
            const matchesCategory = !category || 
                row.querySelector(`[data-category="${category}"]`) ||
                rowCategory.includes(category.toLowerCase());
            
            if (matchesSearch && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide empty state
        const emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    categoryFilter.addEventListener('change', performSearch);

    // Clear search when input is empty
    searchInput.addEventListener('input', function() {
        if (this.value === '') {
            performSearch();
        }
    });
});
</script>
@endpush