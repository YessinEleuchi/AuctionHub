<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Listing;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{
    public function getListingsStats()
{
    $totalListings = Listing::count();
    $activeListings = Listing::where('active', true)->count();

    return response()->json([
        'success' => true,
        'total_listings' => $totalListings,
        'active_listings' => $activeListings
    ]);
}






public function getUserStats()
{
    $totalUsers = User::count();

    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    $newUsersThisMonth = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

    $percentage = $totalUsers > 0 ? round(($newUsersThisMonth / $totalUsers) * 100, 2) : 0;

    return response()->json([
        'success' => true,
        'total_users' => $totalUsers,
        'new_users_this_month' => $newUsersThisMonth,
        'percentage_this_month' => $percentage
    ]);
}


public function getTotalRevenue()
{
    $now = Carbon::now();
    // On récupère tous les listings déjà fermés
    $closedListings = Listing::where('closing_date', '<', $now)->get();

    $totalRevenue = 0;

    foreach ($closedListings as $listing) {
        $highestBid = $listing->bids()->orderByDesc('amount')->first();

        if ($highestBid) {
            $totalRevenue += $highestBid->amount;
        }
    }

    return response()->json([
        'success' => true,
        'total_revenue' => $totalRevenue
    ]);
}



public function getTotalRevenueAndConversionRate()
{
    $now = Carbon::now();
    $startOfMonth = $now->copy()->startOfMonth();
    $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
    $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

    $totalRevenue = 0;
    $convertedThisMonth = 0;
    $closedThisMonth = 0;

    $convertedLastMonth = 0;
    $closedLastMonth = 0;

    // Listings fermés ce mois-ci
    $closedThisMonthListings = Listing::whereBetween('closing_date', [$startOfMonth, $now])->get();

    foreach ($closedThisMonthListings as $listing) {
        $closedThisMonth++;
        $highestBid = $listing->bids()->orderByDesc('amount')->first();
        if ($highestBid) {
            $convertedThisMonth++;
            $totalRevenue += $highestBid->amount;
        }
    }

    // Listings fermés le mois dernier
    $closedLastMonthListings = Listing::whereBetween('closing_date', [$startOfLastMonth, $endOfLastMonth])->get();

    foreach ($closedLastMonthListings as $listing) {
        $closedLastMonth++;
        $highestBid = $listing->bids()->orderByDesc('amount')->first();
        if ($highestBid) {
            $convertedLastMonth++;
        }
    }

    // Calculs de taux
    $conversionRateThisMonth = $closedThisMonth > 0 ? ($convertedThisMonth / $closedThisMonth) * 100 : 0;
    $conversionRateLastMonth = $closedLastMonth > 0 ? ($convertedLastMonth / $closedLastMonth) * 100 : 0;

    $conversionChange = $conversionRateThisMonth - $conversionRateLastMonth;

    return response()->json([
        'success' => true,
        'total_revenue' => round($totalRevenue, 2),
        'conversion_rate_this_month' => round($conversionRateThisMonth, 2),
        'conversion_rate_last_month' => round($conversionRateLastMonth, 2),
        'conversion_change_percentage' => round($conversionChange, 2)
    ]);
}


public function getLastThreeListings()
{
    $now = Carbon::now();

    $listings = Listing::where(function ($query) use ($now) {
            $query->where('active', true)
                  ->orWhere('closing_date', '<', $now);
        })
        ->orderBy('closing_date', 'desc')
        ->limit(3)
        ->get();

    return response()->json([
        'success' => true,
        'listings' => $listings
    ]);
}
public function getLastThreeUsers()
{
    $users = User::orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

    return response()->json([
        'success' => true,
        'latest_users' => $users
    ]);
}

public function getLastThreeBids()
{
    $bids = Bid::with(['user.avatar', 'listing.images']) // Charge les relations user et listing
        ->orderByDesc('created_at')  // Trie par date de création (les plus récentes en premier)
        ->limit(3)                   // Limite à 3 résultats
        ->get();

    return response()->json([
        'success' => true,
        'latest_bids' => $bids
    ]);
}

public function deleteUserById($id)
{
    // Cherche l'utilisateur par son ID
    $user = User::find($id);

    // Vérifie si l'utilisateur existe
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    // Supprime l'utilisateur
    $user->delete();

    // Retourne une réponse de succès
    return response()->json([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
}

/**
     * Récupérer toutes les annonces avec leurs relations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllListings()
    {
        $listings = Listing::with(['auctioneer', 'category', 'bids', 'watchlists', 'images'])->get();
        return response()->json($listings);
    }

    /**
     * Récupérer une annonce par son ID avec ses relations.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListingById($id)
    {
        $listing = Listing::with(['auctioneer', 'category', 'bids', 'watchlists', 'images'])->find($id);

        if (!$listing) {
            return response()->json(['message' => 'Annonce non trouvée'], 404);
        }

        return response()->json($listing);
    }

    /**
     * Récupérer toutes les enchères avec leurs relations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBids()
    {
        $bids = Bid::with(['user', 'listing'])->get();
        return response()->json($bids);
    }

    /**
     * Récupérer une enchère par son ID avec ses relations.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBidById($id)
    {
        $bid = Bid::with(['user', 'listing'])->find($id);

        if (!$bid) {
            return response()->json(['message' => 'Enchère non trouvée'], 404);
        }

        return response()->json($bid);
    }

    /**
     * Récupérer tous les utilisateurs avec leurs relations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::with(['listings', 'bids', 'watchlists', 'reviews', 'avatar'])->get();
        return response()->json($users);
    }

    /**
     * Récupérer un utilisateur par son ID avec ses relations.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserById($id)
    {
        $user = User::with(['listings', 'bids', 'watchlists', 'reviews', 'avatar'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json($user);
    }

}
