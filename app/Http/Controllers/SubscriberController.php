<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function getOrCreate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email'
        ]);

        $subscriber = Subscriber::firstOrCreate(
            ['email' => $request->email]
        );

        return response()->json($subscriber, 201);
    }
}
