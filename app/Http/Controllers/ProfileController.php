<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            // Validate file size (2MB max)
            if ($photo->getSize() > 2 * 1024 * 1024) {
                return back()->withErrors(['photo' => 'The photo must not be larger than 2MB.']);
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($photo->getMimeType(), $allowedTypes)) {
                return back()->withErrors(['photo' => 'The photo must be a file of type: jpeg, png, gif, webp.']);
            }

            try {
                            // Delete old photo if exists (handle both full URLs and bare paths)
            if ($request->user()->photo) {
                $old = $request->user()->photo;
                $oldPath = \Illuminate\Support\Str::contains($old, '/storage/')
                    ? \Illuminate\Support\Str::after($old, '/storage/')
                    : ltrim(str_replace('storage/', '', $old), '/');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    \Log::info('Deleted old profile photo', ['path' => $oldPath]);
                }
            }

                // Generate unique filename
                $filename = 'profile-' . $request->user()->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
                
                            // Store new photo in public disk for simpler path structure
            $path = $photo->storeAs('profile-photos', $filename, 'public');
                
                            // Verify the file was stored successfully
            if (!Storage::disk('public')->exists($path)) {
                \Log::error('Failed to store profile photo', ['path' => $path, 'user_id' => $request->user()->id]);
                return back()->withErrors(['photo' => 'Failed to store the photo. Please try again.']);
            }
                
                            // Store a full absolute link on the current domain
            $request->user()->photo = asset('storage/' . $path);
                
                // Log for debugging
                \Log::info('Profile photo uploaded successfully', [
                    'user_id' => $request->user()->id,
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $request->user()->photo,
                    'file_size' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType()
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error uploading profile photo', [
                    'user_id' => $request->user()->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['photo' => 'An error occurred while uploading the photo. Please try again.']);
            }
        }

        $request->user()->save();
        toastr()->success(__('master.profile_updated'));
        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete user's photo if exists
        if ($user->photo) {
            $path = str_replace('/storage/', 'profile-photos/', $user->photo);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
