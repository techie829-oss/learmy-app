<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceGoogleToken;
use App\Modules\Integrations\Models\IntegrationConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientGoogleOAuthController extends Controller
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function redirect(Request $request)
    {
        $config = IntegrationConfig::forProvider('google_workspace');
        if (! $config || ! $config->enabled) {
            return redirect()->back()->with('error', 'Google Workspace integration is not enabled on the system.');
        }

        $creds = $config->credentials ?? [];
        $clientId = (string) ($creds['client_id'] ?? '');

        if ($clientId === '') {
            return redirect()->back()->with('error', 'Google Client ID is not configured by system administrator.');
        }

        $redirectUri = route('client.integrations.google.callback');
        $scopes = implode(' ', [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/userinfo.email',
        ]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopes,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => csrf_token(),
        ]);

        return redirect(self::AUTH_URL.'?'.$query);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('client.integrations.index')->with('error', 'Google authorization cancelled: '.$request->input('error'));
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('client.integrations.index')->with('error', 'Missing authorization code from Google.');
        }

        $config = IntegrationConfig::forProvider('google_workspace');
        $creds = $config?->credentials ?? [];
        $clientId = (string) ($creds['client_id'] ?? '');
        $clientSecret = (string) ($creds['client_secret'] ?? '');

        $redirectUri = route('client.integrations.google.callback');

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if (! $response->successful() || ! $response->json('refresh_token')) {
            Log::error('Client Google OAuth Token Exchange Failed', ['body' => $response->body()]);
            return redirect()->route('client.integrations.index')->with('error', 'Failed to obtain Google Refresh Token. Please try again.');
        }

        $user = $request->user();
        $workspaceId = $user->current_workspace_id ?? $user->workspace_id;

        $refreshToken = $response->json('refresh_token');
        $accessToken = $response->json('access_token');
        $expiresIn = $response->json('expires_in', 3600);

        // Fetch user email from Google UserInfo
        $userInfo = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $googleEmail = $userInfo->json('email') ?? $user->email;

        WorkspaceGoogleToken::updateOrCreate(
            ['workspace_id' => $workspaceId],
            [
                'email' => $googleEmail,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => now()->addSeconds($expiresIn),
            ]
        );

        return redirect()->route('client.integrations.index')->with('success', "Google Calendar connected successfully as {$googleEmail}!");
    }

    public function disconnect(Request $request)
    {
        $user = $request->user();
        $workspaceId = $user->current_workspace_id ?? $user->workspace_id;

        WorkspaceGoogleToken::where('workspace_id', $workspaceId)->delete();

        return redirect()->route('client.integrations.index')->with('success', 'Google Calendar disconnected.');
    }
}
