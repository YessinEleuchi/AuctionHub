<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Models\Listing;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function getAll()
    {
        return Listing::orderBy('created_at', 'desc')->get();
    }

    public function getByUserId(Request $request)
    {
        $userId = $request->query('user_id');
        $watchlist = Watchlist::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($watchlist);
    }

    public function getBySessionKey(Request $request)
    {
        $sessionKey = $request->query('session_key');
        $userId = $request->query('user_id');

        $excluded = Watchlist::where('user_id', $userId)->pluck('listing_id');

        $watchlist = Watchlist::where('session_key', $sessionKey)
            ->whereNotIn('listing_id', $excluded)
            ->pluck('listing_id');

        return response()->json($watchlist);
    }

    public function getByClientId($clientId)
    {
        if (!$clientId) return response()->json([]);

        $watchlist = Watchlist::where(function ($query) use ($clientId) {
            $query->where('user_id', $clientId)
                  ->orWhere('session_key', $clientId);
        })
        ->orderBy('created_at', 'desc')
        ->with(['listing.category', 'listing.auctioneer', 'listing.image'])
        ->get();

        return response()->json($watchlist);
    }

    public function getByClientIdAndListingId(Request $request, $listingId)
    {
        $clientId = $request->query('client_id');

        if (!$clientId) return response()->json(null);

        $watchlist = Watchlist::where('listing_id', $listingId)
            ->where(function ($query) use ($clientId) {
                $query->where('user_id', $clientId)
                      ->orWhere('session_key', $clientId);
            })
            ->with(['listing.category', 'listing.auctioneer', 'listing.image'])
            ->first();

        return response()->json($watchlist);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'session_key' => 'nullable|string',
            'listing_id' => 'required|exists:listings,id',
        ]);

        $clientId = $data['user_id'] ?? $data['session_key'];

        $existing = Watchlist::where('listing_id', $data['listing_id'])
            ->where(function ($query) use ($clientId) {
                $query->where('user_id', $clientId)
                      ->orWhere('session_key', $clientId);
            })
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(null);
        } else {
            $watchlist = Watchlist::create($data);
            return response()->json($watchlist, 201);
        }
    }

    public function bulkCreate(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
        ]);

        Watchlist::insertOrIgnore($data['items']);
        return response()->json(['message' => 'Bulk insert done']);
    }

    public function delete($id)
    {
        Watchlist::where('id', $id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function deleteAll()
    {
        Watchlist::truncate();
        return response()->json(['message' => 'All watchlist deleted']);
    }
}
