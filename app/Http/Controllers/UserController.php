<?php

namespace App\Http\Controllers;

use App\Mail\ActivationEmail;
use App\Mail\WelcomeEmail;
use App\Mail\PasswordResetOtp;
use App\Mail\PasswordResetSuccess;
use App\Models\File;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'terms_agreement' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'terms_agreement' => $request->terms_agreement,
                'is_email_verified' => false,
            ]);

            // Envoyer un email d'activation
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user));

            // Générer token JWT
            $token = JWTAuth::fromUser($user);

            // Structurer la réponse comme dans NestJS
            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Vérifier mot de passe
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Vérifier que l'email est validé
        if (!$user->is_email_verified) {
            return response()->json(['error' => 'Verify your email first'], 401);
        }

        // Authentifier et générer le token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'tokens' => [
                    'access' => $token,
                    'refresh' => Str::random(60), // si tu veux un refresh token aléatoire juste côté client
                ]
            ]
        ], 200);

    }
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        // Récupérer l'utilisateur par email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Incorrect Email'], 404);
        }

        if ($user->isEmailVerified) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        // Vérifier l'OTP
        $otp = Otp::where('user_id', $user->id)->first();
        if (!$otp || $otp->code !== $request->otp) {
            return response()->json(['error' => 'Incorrect OTP'], 400);
        }

        // Vérifier si l'OTP a expiré
        if ($this->checkOtpExpiration($otp)) {
            return response()->json(['error' => 'Expired OTP'], 400);
        }

        // Vérifier l'email et supprimer l'OTP
        $user->isEmailVerified = true;
        $user->save();
        $otp->delete();

        // Envoyer un email de bienvenue
        Mail::to($user->email)->send(new WelcomeEmail($user));

        return response()->json(['message' => 'Account verification successful'], 200);
    }
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Vérifier l'utilisateur
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Email',
            ], 404);
        }

        if ($user->isEmailVerified) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified',
            ]);
        }

        // Générer un nouvel OTP
        $otp = rand(100000, 999999);

        // Mettre à jour ou créer le code OTP
        Otp::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $otp, 'expires_at' => now()->addMinutes(10)]
        );

        // Envoyer l'email d'activation
        Mail::to($user->email)->send(new ActivationEmail($user, $otp));

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent',
        ]);
    }
    public function setNewPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed', // nécessite un champ password_confirmation
        ]);

        // 1. Trouver l'utilisateur
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Email',
            ], 404);
        }

        // 2. Vérifier le code OTP
        $otp = Otp::where('user_id', $user->id)->first();
        if (!$otp || $otp->code !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect OTP',
            ], 400);
        }

        // 3. Vérifier l'expiration
        if (now()->greaterThan($otp->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Expired OTP',
            ], 400);
        }

        // 4. Supprimer l'OTP
        $otp->delete();

        // 5. Mettre à jour le mot de passe et vérifier l'email
        $user->password = Hash::make($request->password);
        $user->isEmailVerified = true;
        $user->save();

        // 6. Envoyer un email de confirmation
        Mail::to($user->email)->send(new \PasswordResetSuccess($user));

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful',
        ]);
    }
    public function sendPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Email',
            ], 404);
        }

        // Générer et sauvegarder un nouveau OTP
        $otpCode = rand(100000, 999999);
        $user->otp()->updateOrCreate([], [
            'code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Envoyer l'email de reset password
        Mail::to($user->email)->send(new \PasswordResetOtp($user, $otpCode));

        return response()->json([
            'success' => true,
            'message' => 'Password otp sent',
        ]);
    }
    public function retrieveProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'User details fetched!',
            'data' => $user,
        ]);
    }
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $dataToUpdate = collect($request->all())->filter(fn($value) => !is_null($value))->toArray();

        if ($request->has('fileType')) {
            $fileType = $request->input('fileType');

            if ($user->avatar_id) {
                $file = File::find($user->avatar_id);
                if ($file) {
                    $file->resource_type = $fileType;
                    $file->save();
                } else {
                    $file = File::create(['resource_type' => $fileType]);
                }
            } else {
                $file = File::create(['resource_type' => $fileType]);
            }

            $dataToUpdate['avatar_id'] = $file->id;
            unset($dataToUpdate['fileType']);
            $fileUpload = true;
        } else {
            $fileUpload = false;
        }

        // ✅ Intelephense comprendra avec cette annotation
        $user->update($dataToUpdate);

        $userDict = $user->toArray();
        $userDict['fileUpload'] = $fileUpload;

        return response()->json([
            'success' => true,
            'message' => 'User updated!',
            'data' => $userDict,
        ]);
    }



















    /**
     * Display a listing of users.
     */
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Register a new user.
     */
   /* public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'terms_agreement' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'terms_agreement' => $request->terms_agreement,
            ]);

            $token = JWTAuth::fromUser($user);
            return response()->json(compact('user', 'token'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }*/





    /**
     * User login and token generation.
     */





    /**
     * Logout the user (invalidate token).
     */


    /**
     * Get the authenticated user.
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user, 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update($request->all());
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Un lien de réinitialisation a été envoyé à votre email.'], 200)
            : response()->json(['message' => 'Erreur lors de l\'envoi de l\'email.'], 500);
    }


    public function resetPassword(Request $request)
    {
        // Validation de la requête
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Réinitialisation du mot de passe via Password Broker
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Votre mot de passe a été réinitialisé avec succès.'], 200)
            : response()->json(['message' => 'Échec de la réinitialisation du mot de passe.'], 400);
    }


    /**
     * Vérifie si l'OTP est expiré
     */
    private function checkOtpExpiration($otp)
    {
        $expirationTime = now()->subMinutes(10); // Exemple : 10 minutes de validité
        return $otp->created_at < $expirationTime;
    }








}
