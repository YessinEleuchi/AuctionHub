<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Récupérer les derniers fichiers créés.
     */
    public function getLatestIds($amount)
    {
        $files = File::orderBy('created_at', 'desc')->take($amount)->pluck('id');
        return response()->json($files);
    }

    /**
     * Créer un nouveau fichier.
     */
    public function create(Request $request)
    {
        $file = File::create($request->all());
        return response()->json($file, 201);
    }

    /**
     * Créer plusieurs fichiers en une seule requête.
     */
    public function bulkCreate(Request $request)
    {
        File::insert($request->all());
        return response()->json(['message' => 'Fichiers ajoutés avec succès'], 201);
    }

    /**
     * Mettre à jour un fichier.
     */
    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);
        $file->update($request->all());
        return response()->json($file);
    }

    /**
     * Tester la création d'un fichier.
     */
    public function testFile()
    {
        $file = File::create(['resourceType' => 'image/jpeg']);
        return response()->json($file, 201);
    }
}
