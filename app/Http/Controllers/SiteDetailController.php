<?php

namespace App\Http\Controllers;

use App\Models\SiteDetail;
use Illuminate\Http\Request;

class SiteDetailController extends Controller
{
    public function get()
    {
        $siteDetail = SiteDetail::first();

        if (!$siteDetail) {
            $siteDetail = SiteDetail::create();
        }

        return response()->json($siteDetail);
    }
}
