<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function getActive()
    {
        $reviews = Review::where('show', true)
            ->with('reviewer:id,firstName,lastName,avatar')
            ->select('text', 'reviewer_id')
            ->get();

        return response()->json($reviews);
    }

    public function getCount()
    {
        $count = Review::where('show', true)->count();
        return response()->json(['count' => $count]);
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'reviews' => 'required|array',
            'reviews.*.text' => 'required|string',
            'reviews.*.show' => 'required|boolean',
            'reviews.*.reviewer_id' => 'required|exists:users,id',
        ]);

        Review::insert($request->reviews);
        return response()->json(['message' => 'Reviews added successfully'], 201);
    }

    public function create(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'show' => 'required|boolean',
            'reviewer_id' => 'required|exists:users,id',
        ]);

        $review = Review::create($request->all());
        return response()->json($review, 201);
    }
}
