<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtpController extends Controller
{
    /**
     * Récupérer un OTP par ID utilisateur.
     */
    public function getByUserId($id)
    {
        $otp = Otp::where('user_id', $id)->select('id', 'code', 'updated_at')->first();
        return response()->json($otp);
    }

    /**
     * Créer ou mettre à jour un OTP pour un utilisateur.
     */
    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $code = random_int(100000, 999999);

        $otp = Otp::updateOrCreate(
            ['user_id' => $userId],
            ['code' => $code, 'updated_at' => now()]
        );

        return response()->json($otp, 201);
    }

    /**
     * Mettre à jour un OTP.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|integer|min:100000|max:999999',
        ]);

        $otp = Otp::findOrFail($id);
        $otp->update($request->all());

        return response()->json($otp);
    }

    /**
     * Supprimer un OTP.
     */
    public function delete($id)
    {
        $otp = Otp::findOrFail($id);
        $otp->delete();

        return response()->json(['message' => 'OTP supprimé avec succès'], 200);
    }

    /**
     * Vérifier si l'OTP a expiré.
     */
    public function checkOtpExpiration($id)
    {
        $otp = Otp::findOrFail($id);

        $timeDifference = now()->diffInSeconds($otp->updated_at);
        $expirationTime = config('settings.email_otp_expire_seconds', 300); // Par défaut 5 min

        return response()->json([
            'expired' => $timeDifference > $expirationTime,
            'time_difference' => $timeDifference,
        ]);
    }
}
