<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Profile;
use App\Models\Watchlist;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends BaseApiController
{
    /**
     * Get profile of authenticated user
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('Tidak terautentikasi.', [], 401);
            }

            // Eager load profile
            $user->load('profile');

            // If profile does not exist, create a default one
            if (!$user->profile) {
                $profile = Profile::create([
                    'user_id' => $user->id,
                    'full_name' => $user->name ?: 'Administrator',
                    'role' => 'Administrator'
                ]);
                $user->setRelation('profile', $profile);
            }

            return $this->sendResponse($user, 'Profil berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil profil.', [$e->getMessage()], 500);
        }
    }

    /**
     * Update profile details
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('Tidak terautentikasi.', [], 401);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:50',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'role' => 'nullable|string|max:100',
                'photo' => 'nullable|string',
                'profile_photo' => 'nullable|string',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error Validasi.', $validator->errors()->toArray(), 422);
            }

            // Update user details (name & email only)
            $user->update([
                'name' => $request->full_name,
                'email' => $request->email
            ]);

            // Update or create profile details
            $profileData = [
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number ?? $request->phone,
                'company' => $request->company,
                'address' => $request->address,
                'role' => $request->role,
            ];

            if ($request->has('photo')) {
                $profileData['photo'] = $request->photo;
            } elseif ($request->has('profile_photo')) {
                $profileData['photo'] = $request->profile_photo;
            }

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            // Log action in session
            $activities = session()->get('user_activities', []);
            array_unshift($activities, [
                'activity' => 'Updated profile details',
                'timestamp' => now()->toDateTimeString(),
                'ip' => $request->ip()
            ]);
            session()->put('user_activities', array_slice($activities, 0, 10));

            return $this->sendResponse($user->load('profile'), 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui profil.', [$e->getMessage()], 500);
        }
    }

    /**
     * Upload profile avatar
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('Tidak terautentikasi.', [], 401);
            }

            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error Validasi.', $validator->errors()->toArray(), 422);
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                // Save avatar in public folder
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/avatars'), $filename);
                $photoPath = '/uploads/avatars/' . $filename;

                // Delete old photo if exists
                $profile = $user->profile;
                if ($profile && $profile->photo) {
                    $oldPath = public_path(ltrim($profile->photo, '/'));
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                // Update profile
                $profile = Profile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'full_name' => $user->name ?: 'Administrator',
                        'photo' => $photoPath
                    ]
                );

                // Log action
                $activities = session()->get('user_activities', []);
                array_unshift($activities, [
                    'activity' => 'Uploaded new profile photo',
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => $request->ip()
                ]);
                session()->put('user_activities', array_slice($activities, 0, 10));

                return $this->sendResponse(['photo_url' => $photoPath], 'Foto berhasil diunggah.');
            }

            return $this->sendError('Tidak ada foto yang diunggah.', [], 400);
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengunggah foto.', [$e->getMessage()], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('Tidak terautentikasi.', [], 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error Validasi.', $validator->errors()->toArray(), 422);
            }

            // Check old password
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->sendError('Password saat ini tidak valid.', ['current_password' => 'Password saat ini tidak cocok.'], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Log action
            $activities = session()->get('user_activities', []);
            array_unshift($activities, [
                'activity' => 'Changed security password',
                'timestamp' => now()->toDateTimeString(),
                'ip' => $request->ip()
            ]);
            session()->put('user_activities', array_slice($activities, 0, 10));

            return $this->sendResponse(null, 'Password berhasil diubah.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengubah password.', [$e->getMessage()], 500);
        }
    }

    /**
     * Retrieve activity logs
     */
    public function activityLog(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('Tidak terautentikasi.', [], 401);
            }

            // Let's compile dynamic activities + session log
            $sessionLogs = session()->get('user_activities', []);
            
            // Query Watchlist updates
            $watchlists = Watchlist::with('country')->where('user_id', $user->id)->limit(5)->get();
            foreach ($watchlists as $w) {
                $sessionLogs[] = [
                    'activity' => 'Added country to Watchlist: ' . ($w->country ? $w->country->name : 'Unknown'),
                    'timestamp' => $w->created_at ? $w->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'ip' => '127.0.0.1'
                ];
            }

            // Query planned shipments
            $shipments = Shipment::orderBy('created_at', 'desc')->limit(5)->get();
            foreach ($shipments as $s) {
                $sessionLogs[] = [
                    'activity' => 'Registered/Updated Shipment: ' . $s->tracking_number . ' (Status: ' . $s->status . ')',
                    'timestamp' => $s->updated_at ? $s->updated_at->toDateTimeString() : now()->toDateTimeString(),
                    'ip' => '127.0.0.1'
                ];
            }

            // Sort by timestamp
            usort($sessionLogs, function($a, $b) {
                return strcmp($b['timestamp'], $a['timestamp']);
            });

            // Limit to 10 items
            $sessionLogs = array_slice($sessionLogs, 0, 10);

            return $this->sendResponse($sessionLogs, 'Log aktivitas berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil log aktivitas.', [$e->getMessage()], 500);
        }
    }
}
