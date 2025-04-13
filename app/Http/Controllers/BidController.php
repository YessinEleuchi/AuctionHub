<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Listing;
use Illuminate\Http\Request;

class BidController extends Controller
{
    /**
     * Récupérer les enchères par ID d'utilisateur.
     */
    public function getByUserId($userId)
    {
        $bids = Bid::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bids);
    }

    /**
     * Récupérer les enchères par ID de listing.
     */
    public function getByListingId($listingId, Request $request)
    {
        $count = $request->query('count');

        $query = Bid::where('listing_id', $listingId)
            ->with(['user.avatar'])
            ->orderBy('created_at', 'desc');

        if ($count) {
            $query->take($count);
        }

        return response()->json($query->get());
    }

    /**
     * Récupérer une enchère par ID utilisateur et ID listing.
     */
    public function getByUserIdAndListingId($userId, $listingId)
    {
        $bid = Bid::where('user_id', $userId)
            ->where('listing_id', $listingId)
            ->with(['user.avatar'])
            ->first();

        return response()->json($bid);
    }

    /**
     * Créer ou mettre à jour une enchère.
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'listing_id' => 'required|exists:listings,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $bidsCount = Bid::where('listing_id', $data['listing_id'])->count();
        $existingBid = Bid::where('user_id', $data['user_id'])
            ->where('listing_id', $data['listing_id'])
            ->first();

        if ($existingBid) {
            // Mise à jour de l'enchère existante
            $existingBid->update($data);
            $bid = $existingBid;
        } else {
            $bid = Bid::create($data);
            $bidsCount++;
        }

        // Mise à jour du listing avec le nouveau montant le plus élevé et le nombre d'enchères
        Listing::where('id', $bid->listing_id)
            ->update([
                'highest_bid' => $bid->amount,
                'bids_count' => $bidsCount,
            ]);

        return response()->json($bid, 201);
    }

    /**
     * Mettre à jour une enchère.
     */
    public function update(Request $request, $id)
    {
        $bid = Bid::findOrFail($id);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $bid->update($data);
        return response()->json($bid);
    }

    /**
     * Supprimer toutes les enchères.
     */
    public function deleteAll()
    {
        Bid::truncate();
        return response()->json(['message' => 'Toutes les enchères ont été supprimées']);
    }
}
