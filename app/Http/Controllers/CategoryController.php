<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Récupérer toutes les catégories.
     */
    public function getAll()
    {
        $categories = Category::get();
        return response()->json($categories);
    }

    /**
     * Récupérer une catégorie par son nom.
     */
    public function getByName($name)
    {
        $category = Category::where('name', $name)->first();
        return response()->json($category);
    }

    /**
     * Récupérer une catégorie par son slug.
     */
    public function getBySlug($slug)
    {
        $category = Category::where('slug', $slug)->first();
        return response()->json($category);
    }

    /**
     * Récupérer tous les IDs des catégories.
     */
    public function getAllIds()
    {
        $ids = Category::pluck('id');
        return response()->json($ids);
    }

    /**
     * Créer une nouvelle catégorie.
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $slug = $request->slug ?? Str::slug($request->name);
        $existingSlug = Category::where('slug', $slug)->exists();

        if ($existingSlug) {
            $slug .= '-' . Str::random(4);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug
        ]);

        return response()->json($category, 201);
    }

    /**
     * Ajouter plusieurs catégories en une seule requête.
     */
    public function bulkCreate(Request $request)
    {
        $data = $request->validate([
            'categories' => 'required|array',
            'categories.*.name' => 'required|string|unique:categories,name',
        ]);

        $categories = array_map(function ($category) {
            return [
                'name' => $category['name'],
                'slug' => Str::slug($category['name'])
            ];
        }, $data['categories']);

        Category::insert($categories);

        return response()->json(['message' => 'Catégories ajoutées avec succès'], 201);
    }

    /**
     * Tester une catégorie (créer une catégorie "TestCategory" si elle n'existe pas).
     */
    public function testCategory()
    {
        $name = "TestCategory";
        $category = Category::where('name', $name)->first();

        if (!$category) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name)
            ]);
        }

        return response()->json($category);
    }
}
