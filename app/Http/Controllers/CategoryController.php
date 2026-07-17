<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function getUserName()
    {
        return session('user_name');
    }

    public function index()
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        $categories = Category::withCount([
                'notes',
                'notes as completed_notes_count' => fn ($query) => $query->where('status', 'concluida'),
            ])
            ->where('user_name', $this->getUserName())
            ->orderBy('name')
            ->get();

        $totalCategories   = $categories->count();
        $notesCategorized  = $categories->sum('notes_count');
        $notesConcluded    = $categories->sum('completed_notes_count');
        $colorsUsed = $categories->pluck('color')->unique()->count();

        return view('layout.categories', compact(
            'categories',
            'totalCategories',
            'notesCategorized',
            'notesConcluded',
            'colorsUsed'
        ));
    }

    public function store(Request $request)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
        ]);

        Category::create([
            'user_name'   => $this->getUserName(),
            'name'        => $request->name,
            'description' => $request->description,
            'icon'        => $request->icon ?: '📁',
            'color'       => $request->color ?: '#ff7b00',
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('user_name', $this->getUserName())->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
        ]);

        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
            'icon'        => $request->icon ?: $category->icon,
            'color'       => $request->color ?: $category->color,
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada!');
    }

    public function toggle($id)
    {
        $category = Category::where('user_name', $this->getUserName())->findOrFail($id);
        $category->update(['active' => !$category->active]);
        return back();
    }

    public function destroy($id)
    {
        Category::where('user_name', $this->getUserName())->findOrFail($id)->delete();
        return redirect()->route('categories.index')->with('success', 'Categoria removida.');
    }
}
