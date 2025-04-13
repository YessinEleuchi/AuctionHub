<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Watchlist;
use App\Models\Bid;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    // 🔵 1. Récupérer toutes les annonces (page d'accueil)
    public function retrieveListings(Request $request)
    {
        $quantity = $request->query('quantity');

        $listings = Listing::with(['category', 'auctioneer.avatar', 'images'])
            ->orderBy('created_at', 'desc')
            ->take($quantity)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Listings fetched',
            'data' => $listings,
        ]);
    }

    // 🔵 2. Récupérer les détails d'une annonce
    public function retrieveListingDetail($slug)
    {
        $listing = Listing::with(['category', 'auctioneer', 'images'])
            ->where('slug', $slug)
            ->first();

        if (!$listing) {
            return response()->json(['error' => 'Listing does not exist!'], 404);
        }

        $relatedListings = Listing::where('category_id', $listing->category_id)
            ->where('slug', '!=', $slug)
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Listing details fetched',
            'data' => [
                'listing' => $listing,
                'relatedListings' => $relatedListings,
            ],
        ]);
    }

    // 🔵 3. Récupérer toutes les catégories
    public function retrieveCategories()
    {
        $categories = Category::all();
        return response()->json([
            'success' => true,
            'message' => 'Categories fetched',
            'data' => $categories,
        ]);
    }

    // 🔵 4. Récupérer toutes les annonces par catégorie
    public function retrieveListingsByCategories(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['error' => 'Invalid category'], 404);
        }

        $listings = Listing::where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Category Listings fetched',
            'data' => $listings,
        ]);
    }

    // 🔵 5. Récupérer toutes les annonces suivies (watchlist)
    public function retrieveListingsByWatchlist(Request $request)
    {
        $userId = Auth::id();

        $watchlists = Watchlist::with(['listing.images', 'listing.auctioneer.avatar'])
            ->where('user_id', $userId)
            ->get();

        $listings = $watchlists
            ->map(function ($watchlist) {
                return $watchlist->listing;
            })
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Watchlist Listings fetched',
            'data' => $listings,
        ]);
    }

    // 🔵 6. Ajouter ou retirer une annonce à la watchlist
    public function addListingsToWatchlist(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string',
        ]);

        $listing = Listing::where('slug', $data['slug'])->first();

        if (!$listing) {
            return response()->json(['error' => 'Listing does not exist!'], 404);
        }

        $userId = Auth::id();
        $existingWatchlist = Watchlist::where('listing_id', $listing->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingWatchlist) {
            $existingWatchlist->delete();
            $message = 'Listing removed from user watchlist';
        } else {
            Watchlist::create([
                'listing_id' => $listing->id,
                'user_id' => $userId,
            ]);
            $message = 'Listing added to user watchlist';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // 🔵 7. Récupérer les offres (bids) pour une annonce
    public function retrieveListingBids($slug)
    {
        $listing = Listing::where('slug', $slug)->first();

        if (!$listing) {
            return response()->json(['error' => 'Listing does not exist!'], 404);
        }

        $bids = Bid::where('listing_id', $listing->id)->take(3)->get();

        return response()->json([
            'success' => true,
            'message' => 'Listing Bids fetched',
            'data' => [
                'listing' => $listing->name,
                'bids' => $bids,
            ],
        ]);
    }

    // 🔵 8. Ajouter une offre (bid) sur une annonce
    public function createBid(Request $request, $slug)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $listing = Listing::where('slug', $slug)->first();

        if (!$listing) {
            return response()->json(['error' => 'Listing does not exist!'], 404);
        }

        $bid = Bid::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'amount' => $data['amount'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bid added to listing',
            'data' => $bid,
        ]);
    }

    // 🔥 9. (NOUVEAU) Récupérer toutes les annonces du user connecté (Auctioneer)
    public function retrieveAuctioneerListings(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $listings = Listing::where('auctioneer_id', $user->id) // ✅ Utiliser auctioneer_id ici
            ->with(['category', 'image'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Auctioneer listings fetched!',
            'data' => $listings,
        ]);
    }
    public static function getRecentAuctions()
{
    $recentListings = Listing::where('closing_date', '<', Carbon::now())
    ->where('active', false) // facultatif si tu veux les désactivées
    ->orderBy('closing_date', 'desc')
    ->get();

return response()->json([
    'success' => true,
    'data' => $recentListings
]);
}
public static function getRecentAuction()
{
   


        $recentListings = Listing::where('closing_date', '<', Carbon::now())
        ->where('active', false) // facultatif si tu veux les désactivées
        ->orderBy('closing_date', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $recentListings
    ]);
}

public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:listings',
            'desc' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'closing_date' => 'required|date',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,webp',
        ]);

        // Créer le Listing
        $listing = Listing::create([
            'auctioneer_id' => Auth::id(), // ou $request->user()->id
            'name' => $request->name,
            'slug' => $request->slug,
            'desc' => $request->desc,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'highest_bid' => 0,
            'bids_count' => 0,
            'closing_date' => $request->closing_date,
            'active' => false,
        ]);

        // Enregistrer les images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $fileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $finalName = str_replace(' ', '_', $fileName) . '_' . rand() . '_' . time() . '.' . $extension;

                $image->storeAs('public', $finalName);

                File::create([
                    'resource_type' => $finalName,
                    'listing_id' => $listing->id,
                ]);
            }
        }

        // Charger la relation user (auctioneer) et images
        $listing->load(['auctioneer', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Listing created successfully',
            'data' => $listing
        ], 201);
    }




}