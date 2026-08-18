<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    public function index(Request $request)
    {
        $query = Documentation::active();
        
        if ($request->has('search')) {
            $query->search($request->search);
        }
        
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }
        
        $documentation = $query->orderBy('order')->paginate(12);
        $categories = Documentation::active()->distinct()->pluck('category');
        
        return view('documentation.index', compact('documentation', 'categories'));
    }

    public function show($slug)
    {
        $doc = Documentation::where('slug', $slug)->active()->firstOrFail();
        $relatedDocs = Documentation::active()
            ->where('category', $doc->category)
            ->where('id', '!=', $doc->id)
            ->limit(5)
            ->get();
            
        return view('documentation.show', compact('doc', 'relatedDocs'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $results = Documentation::active()
            ->search($request->q)
            ->orderBy('order')
            ->get();

        return response()->json($results);
    }

    public function categories()
    {
        $categories = Documentation::active()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return response()->json($categories);
    }

    public function category($category)
    {
        $docs = Documentation::active()
            ->byCategory($category)
            ->orderBy('order')
            ->get();

        return view('documentation.category', compact('docs', 'category'));
    }

    public function adminIndex()
    {
        $documentation = Documentation::orderBy('order')->paginate(15);
        return view('superadmin.documentation.admin.index', compact('documentation'));
    }

    public function create()
    {
        $categories = Documentation::active()->distinct()->pluck('category');
        return view('superadmin.documentation.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['is_active'] = $request->has('is_active');

        Documentation::create($data);

        return redirect()->route('documentation.admin.index')
            ->with('success', 'Documentation article created successfully.');
    }

    public function edit($id)
    {
        $doc = Documentation::findOrFail($id);
        $categories = Documentation::active()->distinct()->pluck('category');
        return view('superadmin.documentation.admin.edit', compact('doc', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $doc = Documentation::findOrFail($id);
        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['is_active'] = $request->has('is_active');

        $doc->update($data);

        return redirect()->route('documentation.admin.index')
            ->with('success', 'Documentation article updated successfully.');
    }

    public function destroy($id)
    {
        $doc = Documentation::findOrFail($id);
        $doc->delete();

        return redirect()->route('documentation.admin.index')
            ->with('success', 'Documentation article deleted successfully.');
    }
} 