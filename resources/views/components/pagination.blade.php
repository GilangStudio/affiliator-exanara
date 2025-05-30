@if($paginator->hasPages())
<div class="ms-auto">
    <nav aria-label="pagination">
        <ul class="pagination pagination-sm m-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link"><i class="ti ti-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="ti ti-chevron-left"></i></a>
                </li>
            @endif

            {{-- Page Number Links --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                
                // Hitung range halaman yang akan ditampilkan
                $start = max(1, $currentPage - 1);  // 1 halaman sebelum current
                $end = min($lastPage, $currentPage + 1);  // 1 halaman sesudah current
                
                // Adjust jika terlalu dekat dengan awal atau akhir
                if ($currentPage <= 2) {
                    $end = min($lastPage, 3);
                }
                if ($currentPage >= $lastPage - 1) {
                    $start = max(1, $lastPage - 2);
                }
            @endphp

            {{-- First page if not in range --}}
            @if($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if($start > 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif

            {{-- Page Numbers --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last page if not in range --}}
            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="ti ti-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link"><i class="ti ti-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
</div>
@endif