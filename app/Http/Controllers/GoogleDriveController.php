<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Room\Actions\CreateGoogleDriveStorageAccount;
use Domain\Room\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    public function __construct(
        private readonly CreateGoogleDriveStorageAccount $createGoogleDriveStorageAccount
    ) {}

    /**
     * Redirect user to Google OAuth2 authorization page
     */
    public function authorize(Request $request): RedirectResponse
    {
        // Ensure user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to connect Google Drive');
        }

        try {
            // Get the authorization URL
            $authUrl = GoogleDriveService::getAuthorizationUrl();

            // Store the intended redirect URL in session for after authorization
            if ($request->has('redirect_to')) {
                session(['google_drive_redirect_to' => $request->get('redirect_to')]);
            }

            Log::info('Redirecting user to Google Drive authorization', [
                'user_id' => Auth::id(),
            ]);

            return redirect($authUrl);

        } catch (\Exception $e) {
            Log::error('Failed to generate Google Drive authorization URL', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to connect to Google Drive. Please try again.');
        }
    }

    /**
     * Handle OAuth2 callback from Google
     */
    public function callback(Request $request): RedirectResponse
    {
        // Ensure user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Authentication session expired. Please log in and try again.');
        }

        // Check for authorization code
        if (! $request->has('code')) {
            $error = $request->get('error', 'Authorization was denied');

            Log::warning('Google Drive authorization failed or denied', [
                'user_id' => Auth::id(),
                'error' => $error,
            ]);

            return redirect()->route('dashboard') // or appropriate route
                ->with('error', 'Google Drive authorization was denied or failed.');
        }

        try {
            $user = Auth::user();
            $authorizationCode = $request->get('code');

            // Create storage account with the authorization code
            $storageAccount = $this->createGoogleDriveStorageAccount->execute(
                $user,
                $authorizationCode,
                'Google Drive Account' // Default display name
            );

            Log::info('Successfully connected Google Drive account', [
                'user_id' => $user->id,
                'storage_account_id' => $storageAccount->id,
            ]);

            // Get redirect URL from session or default
            $redirectTo = session()->pull('google_drive_redirect_to', route('dashboard'));

            return redirect($redirectTo)
                ->with('success', 'Google Drive account connected successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to process Google Drive callback', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard') // or appropriate route
                ->with('error', 'Failed to connect Google Drive account: '.$e->getMessage());
        }
    }

    /**
     * Disconnect Google Drive account
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $request->validate([
            'storage_account_id' => 'required|integer|exists:user_storage_accounts,id',
        ]);

        try {
            $user = Auth::user();
            $storageAccount = $user->storageAccounts()
                ->where('id', $request->storage_account_id)
                ->where('provider', 'google_drive')
                ->first();

            if (! $storageAccount) {
                return redirect()->back()
                    ->with('error', 'Google Drive account not found.');
            }

            // Delete the storage account
            $storageAccount->delete();

            Log::info('Successfully disconnected Google Drive account', [
                'user_id' => $user->id,
                'storage_account_id' => $storageAccount->id,
            ]);

            return redirect()->back()
                ->with('success', 'Google Drive account disconnected successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to disconnect Google Drive account', [
                'user_id' => Auth::id(),
                'storage_account_id' => $request->storage_account_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to disconnect Google Drive account.');
        }
    }
}
