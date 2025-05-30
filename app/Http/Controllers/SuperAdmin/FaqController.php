<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of FAQs
     */
    public function index()
    {
        $faqs = Faq::global()
            ->ordered()
            ->get();

        return view('pages.superadmin.faqs.index', compact('faqs'));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|in:general,project,payment,technical,commission,account,other',
            'status' => 'boolean'
        ], [
            'question.required' => 'Pertanyaan harus diisi',
            'question.max' => 'Pertanyaan maksimal 255 karakter',
            'answer.required' => 'Jawaban harus diisi',
            'category.required' => 'Kategori harus dipilih',
            'category.in' => 'Kategori tidak valid'
        ]);

        // Get next sort order
        $maxOrder = Faq::global()->max('sort_order') ?: 0;

        $faq = Faq::create([
            'project_id' => null, // Global FAQ
            'type' => 'general',
            'category' => $request->category,
            'question' => $request->question,
            'answer' => $request->answer,
            'sort_order' => $maxOrder + 1,
            'is_active' => $request->boolean('status', true)
        ]);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'create_faq',
            "FAQ baru dibuat: {$faq->question}",
            null,
            ['faq_id' => $faq->id, 'category' => $faq->category]
        );

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'FAQ berhasil dibuat!');
    }

    /**
     * Update the specified FAQ
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|in:general,project,payment,technical,commission,account,other',
            'status' => 'boolean'
        ], [
            'question.required' => 'Pertanyaan harus diisi',
            'question.max' => 'Pertanyaan maksimal 255 karakter',
            'answer.required' => 'Jawaban harus diisi',
            'category.required' => 'Kategori harus dipilih',
            'category.in' => 'Kategori tidak valid'
        ]);

        $oldData = $faq->only(['question', 'answer', 'category', 'is_active']);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'is_active' => $request->boolean('status', true)
        ]);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_faq',
            "FAQ diperbarui: {$faq->question}",
            null,
            ['faq_id' => $faq->id, 'old_data' => $oldData, 'new_data' => $faq->only(['question', 'answer', 'category', 'is_active'])]
        );

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'FAQ berhasil diperbarui!');
    }

    /**
     * Remove the specified FAQ
     */
    public function destroy(Faq $faq)
    {
        $faqQuestion = $faq->question;

        $faq->delete();

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'delete_faq',
            "FAQ dihapus: {$faqQuestion}",
            null,
            ['faq_question' => $faqQuestion]
        );

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'FAQ berhasil dihapus!');
    }

    /**
     * Reorder FAQs
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:faqs,id',
            'orders.*.order' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->orders as $orderData) {
                    Faq::where('id', $orderData['id'])
                        ->update(['sort_order' => $orderData['order']]);
                }
            });

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'reorder_faqs',
                'Urutan FAQ diperbarui',
                null,
                ['total_faqs' => count($request->orders)]
            );

            return response()->json(['success' => true, 'message' => 'Urutan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui urutan'], 500);
        }
    }

    /**
     * Toggle FAQ status
     */
    public function toggleStatus(Faq $faq)
    {
        $newStatus = !$faq->is_active;
        $faq->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'toggle_faq_status',
            "FAQ {$faq->question} {$statusText}",
            null,
            ['faq_id' => $faq->id, 'new_status' => $newStatus]
        );

        return back()->with('success', "FAQ berhasil {$statusText}!");
    }

    /**
     * Get FAQ statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => Faq::global()->count(),
            'active' => Faq::global()->active()->count(),
            'inactive' => Faq::global()->where('is_active', false)->count(),
            'by_category' => Faq::global()
                ->select('category', DB::raw('count(*) as total'))
                ->groupBy('category')
                ->pluck('total', 'category')
                ->toArray()
        ];

        return response()->json($stats);
    }

    /**
     * Search FAQs
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category');
        
        $faqs = Faq::global()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('question', 'LIKE', "%{$query}%")
                             ->orWhere('answer', 'LIKE', "%{$query}%");
                });
            })
            ->when($category, function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $faqs,
            'total' => $faqs->count()
        ]);
    }
}