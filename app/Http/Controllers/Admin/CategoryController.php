<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select(['id', 'name', 'parent_id', 'order'])
            ->with('parent:id,name')
            ->orderBy('order', 'asc')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.categories.create', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $category = new Category();
        $category->name = $data['name'];
        $category->parent_id = $data['parent_id'] ?? null;
        $category->slug = $this->uniqueSlug($data['name']);
        $category->page_title = $data['page_title'] ?? null;
        $category->page_description = $data['page_description'] ?? null;
        $category->page_keywords = $data['page_keywords'] ?? null;
        $category->order = $data['order'] ?? 0;
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori eklendi.');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();

        $parentId = $data['parent_id'] ?? null;
        if ($parentId && (int) $parentId === (int) $category->id) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'Kategori kendisinin üst kategorisi olamaz.']);
        }

        $category->name = $data['name'];
        $category->parent_id = $parentId;
        $category->slug = $this->uniqueSlug($data['name'], $category->id);
        $category->page_title = $data['page_title'] ?? null;
        $category->page_description = $data['page_description'] ?? null;
        $category->page_keywords = $data['page_keywords'] ?? null;
        $category->order = $data['order'] ?? 0;
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori guncellendi.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $hasChildren = Category::where('parent_id', $category->id)->exists();
        if ($hasChildren) {
            return back()->with('error', 'Alt kategorisi olan kategori silinemez.');
        }

        $hasPosts = Post::where('category_id', $category->id)->exists();
        if ($hasPosts) {
            return back()->with('error', 'Bu kategoriye bagli haberler var. Once haberleri tasiyin veya silin.');
        }

        $category->delete();

        return back()->with('success', 'Kategori silindi.');
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
