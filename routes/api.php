<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/verify-email', [UserController::class, 'verifyEmail']);
    Route::post('/resend-verification-email', [UserController::class, 'resendVerificationEmail']);
    Route::post('/set-new-password', [UserController::class, 'setNewPassword']);
    Route::post('/send-password-reset-otp', [UserController::class, 'sendPasswordResetOtp']);
});

// ✅ PROTÉGER LES ROUTES /auctioneer PAR auth:api
Route::middleware('jwt.auth')->group(function () {
    Route::get('/auctioneer', [UserController::class, 'retrieveProfile']);
    Route::put('/auctioneer', [UserController::class, 'updateProfile']);
    Route::get('/auctioneer/listings', [ListingController::class, 'retrieveAuctioneerListings']);

});

// Listings, Watchlist, etc.
Route::prefix('')->group(function () {
    Route::get('listings', [ListingController::class, 'retrieveListings']);
    Route::get('listings/detail/{slug}', [ListingController::class, 'retrieveListingDetail']);
    Route::get('listings/categories', [ListingController::class, 'retrieveCategories']);
    Route::get('listings/categories/{slug}', [ListingController::class, 'retrieveListingsByCategories']);
    Route::get('listings/watchlist', [ListingController::class, 'retrieveListingsByWatchlist']);
    Route::post('listings/watchlist', [ListingController::class, 'addListingsToWatchlist']);
    Route::get('listings/detail/{slug}/bids', [ListingController::class, 'retrieveListingBids']);
    Route::post('listings/detail/{slug}/bids', [ListingController::class, 'createBid']);
});

Route::post('/add', [ListingController::class, 'store']);

Route::get('/categories', [CategoryController::class, 'getAll']);
Route::get('/categories/ids', [CategoryController::class, 'getAllIds']);
Route::get('/categories/name/{name}', [CategoryController::class, 'getByName']);
Route::get('/categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
Route::post('/categories/create', [CategoryController::class, 'create']);
Route::post('/categories/bulk-create', [CategoryController::class, 'bulkCreate']);
Route::get('/categories/test', [CategoryController::class, 'testCategory']);
Route::put('/listings/disapprove/{id}', [ListingController::class, 'disapprove']);



Route::get('/getuser', [UserController::class, 'me']); // Profil utilisateur connecté

Route::get('/listings/recents', [ListingController::class, 'getRecentAuctions']);
Route::get('/listings/recent', [ListingController::class, 'getRecentAuction']);
Route::get('/users', [UserController::class, 'index']); // Liste des utilisateurs
Route::get('admin/listings/stats', [AdminController::class, 'getListingsStats']);
Route::get('admin/users/stats', [AdminController::class, 'getUserStats']);
Route::get('admin/listings/revenue', [AdminController::class, 'getTotalRevenue']);
Route::get('admin/listings/revenueparmoins', [AdminController::class, 'getTotalRevenueAndConversionRate']);
Route::get('admin/listings/getLastThreeListings', [AdminController::class, 'getLastThreeListings']);
Route::get('admin/listings/getLastThreeUsers', [AdminController::class, 'getLastThreeUsers']);
Route::get('admin/bids/latest', [AdminController::class, 'getLastThreeBids']);
Route::delete('admin/users/{id}', [AdminController::class, 'deleteUserById']);
// Récupérer toutes les annonces
Route::get('/admin/listings', [AdminController::class, 'getAllListings']);

// Récupérer une annonce par ID
Route::get('/admin/listings/{id}', [AdminController::class, 'getListingById']);

// Récupérer toutes les enchères
Route::get('/admin/bids', [AdminController::class, 'getAllBids']);

// Récupérer une enchère par ID
Route::get('/admin/bids/{id}', [AdminController::class, 'getBidById']);

// Récupérer tous les utilisateurs
Route::get('/admin/users', [AdminController::class, 'getAllUsers']);

// Récupérer un utilisateur par ID
Route::get('/admin/users/{id}', [AdminController::class, 'getUserById']);
Route::get('/purchase-history', [ListingController::class, 'getPurchaseHistory']);


/*




getlistingbycategory
recherche par name
addwtchlist





Route::get('/getuser', [UserController::class, 'me']); // Profil utilisateur connecté
Route::put('/update/{id}', [UserController::class, 'update']); // Mise à jour d'un utilisateur
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Suppression d'un utilisateur
Route::get('/users', [UserController::class, 'index']); // Liste des utilisateurs







Route::get('/files/latest/{amount}', [FileController::class, 'getLatestIds']);
Route::post('/files/create', [FileController::class, 'create']);
Route::post('/files/bulkCreate', [FileController::class, 'bulkCreate']);
Route::put('/files/update/{id}', [FileController::class, 'update']);
Route::post('/files/test', [FileController::class, 'testFile']);



use App\Http\Controllers\OtpController;

Route::get('/otp/user/{id}', [OtpController::class, 'getByUserId']);
Route::post('/otp/create', [OtpController::class, 'create']);
Route::put('/otp/update/{id}', [OtpController::class, 'update']);
Route::delete('/otp/delete/{id}', [OtpController::class, 'delete']);
Route::get('/otp/check-expiration/{id}', [OtpController::class, 'checkOtpExpiration']);




use App\Http\Controllers\CategoryController;

Route::get('/categories', [CategoryController::class, 'getAll']);
Route::get('/categories/ids', [CategoryController::class, 'getAllIds']);
Route::get('/categories/name/{name}', [CategoryController::class, 'getByName']);
Route::get('/categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
Route::post('/categories/create', [CategoryController::class, 'create']);
Route::post('/categories/bulk-create', [CategoryController::class, 'bulkCreate']);
Route::get('/categories/test', [CategoryController::class, 'testCategory']);





Route::get('/listings', [ListingController::class, 'getAll']);
Route::get('/listings/auctioneer/{auctioneerId}', [ListingController::class, 'getByAuctioneerId']);
Route::get('/listings/slug/{slug}', [ListingController::class, 'getBySlug']);
Route::get('/listings/related/{categoryId}/{slug}/{quantity}', [ListingController::class, 'getRelatedListings']);
Route::get('/listings/category/{categoryId}', [ListingController::class, 'getByCategory']);
Route::get('/listings/count', [ListingController::class, 'getCount']);
Route::post('/listings/create', [ListingController::class, 'create']);
Route::put('/listings/update/{id}', [ListingController::class, 'update']);
Route::post('/listings/bulk-create', [ListingController::class, 'bulkCreate']);
Route::post('/listings/test', [ListingController::class, 'testListing']);



use App\Http\Controllers\BidController;

Route::get('/bids/user/{userId}', [BidController::class, 'getByUserId']);
Route::get('/bids/listing/{listingId}', [BidController::class, 'getByListingId']);
Route::get('/bids/{userId}/{listingId}', [BidController::class, 'getByUserIdAndListingId']);
Route::post('/bids/create', [BidController::class, 'create']);
Route::put('/bids/update/{id}', [BidController::class, 'update']);
Route::delete('/bids/delete-all', [BidController::class, 'deleteAll']);



use App\Http\Controllers\WatchlistController;

Route::prefix('watchlist')->group(function () {
    Route::get('/all', [WatchlistController::class, 'getAll']);
    Route::get('/user', [WatchlistController::class, 'getByUserId']);
    Route::get('/session', [WatchlistController::class, 'getBySessionKey']);
    Route::get('/client/{clientId}', [WatchlistController::class, 'getByClientId']);
    Route::get('/client/{listingId}/find', [WatchlistController::class, 'getByClientIdAndListingId']);
    Route::post('/create', [WatchlistController::class, 'create']);
    Route::post('/bulk-create', [WatchlistController::class, 'bulkCreate']);
    Route::delete('/delete/{id}', [WatchlistController::class, 'delete']);
    Route::delete('/delete-all', [WatchlistController::class, 'deleteAll']);
});


use App\Http\Controllers\SubscriberController;

Route::post('/subscriber', [SubscriberController::class, 'getOrCreate']);



use App\Http\Controllers\ReviewController;

Route::get('/reviews/active', [ReviewController::class, 'getActive']);
Route::get('/reviews/count', [ReviewController::class, 'getCount']);
Route::post('/reviews/bulk', [ReviewController::class, 'bulkCreate']);
Route::post('/reviews', [ReviewController::class, 'create']);



use App\Http\Controllers\SiteDetailController;

Route::get('/site-detail', [SiteDetailController::class, 'get']);
*/
