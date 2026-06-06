<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category');
        
        $query = Faq::with('category')->active()->ordered();
        
        if ($search) {
            $query->search($search);
        }
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $faqs = $query->paginate(10);
        $categories = FaqCategory::active()->ordered()->get();
        
        return view('laboratory.faq.index', compact('faqs', 'categories', 'search', 'categoryId'));
    }

    public function show($id)
    {
        $faq = Faq::with('category')->findOrFail($id);
        $faq->incrementViews();
        
        $relatedFaqs = Faq::with('category')
            ->where('category_id', $faq->category_id)
            ->where('id', '!=', $faq->id)
            ->active()
            ->limit(5)
            ->get();
        
        return view('laboratory.faq.show', compact('faq', 'relatedFaqs'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $faqs = Faq::with('category')
            ->active()
            ->search($search)
            ->ordered()
            ->limit(10)
            ->get();
        
        return response()->json($faqs);
    }

    public function markHelpful(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->markHelpful();
        
        return response()->json([
            'success' => true,
            'helpful_count' => $faq->helpful_count,
            'not_helpful_count' => $faq->not_helpful_count,
            'helpfulness_percentage' => $faq->helpfulness_percentage
        ]);
    }

    public function markNotHelpful(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->markNotHelpful();
        
        return response()->json([
            'success' => true,
            'helpful_count' => $faq->helpful_count,
            'not_helpful_count' => $faq->not_helpful_count,
            'helpfulness_percentage' => $faq->helpfulness_percentage
        ]);
    }

    public function category($slug)
    {
        $category = FaqCategory::where('slug', $slug)->active()->firstOrFail();
        $faqs = $category->activeFaqs()->ordered()->paginate(10);
        
        return view('laboratory.faq.category', compact('category', 'faqs'));
    }
}
