<?php

namespace App\Modules\Whatsapp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Whatsapp\Services\WhatsappQrDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappQrController extends Controller
{
    public function __construct(
        private readonly WhatsappQrDriver $qrDriver
    ) {}

    public function start(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        // Find or create ChannelAccount for QR WhatsApp
        $account = ChannelAccount::firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'channel' => 'whatsapp',
                'provider' => 'qr_baileys',
            ],
            [
                'display_name' => 'WhatsApp QR Account',
                'status' => 'inactive',
                'meta_json' => [
                    'session_id' => 'workspace_'.$workspaceId.'_qr',
                ],
            ]
        );

        $sessionId = $account->meta_json['session_id'] ?? ('workspace_'.$workspaceId.'_qr');

        // Start session in Node QR service
        $res = $this->qrDriver->startSession($sessionId);

        return response()->json([
            'success' => true,
            'channel_account_id' => $account->id,
            'session_id' => $sessionId,
            'status' => $res['status'] ?? 'initializing',
            'qr' => $res['qr'] ?? null,
            'user' => $res['user'] ?? null,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $sessionId = 'workspace_'.$workspaceId.'_qr';

        $account = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('channel', 'whatsapp')
            ->where('provider', 'qr_baileys')
            ->first();

        $res = $this->qrDriver->getSessionStatus($sessionId);

        return response()->json([
            'status' => $res['status'] ?? ($account?->status ?? 'disconnected'),
            'qr' => $res['qr'] ?? ($account?->meta_json['qr_code'] ?? null),
            'user' => $res['user'] ?? ($account?->meta_json['user_info'] ?? null),
            'account' => $account,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $sessionId = 'workspace_'.$workspaceId.'_qr';

        $this->qrDriver->logoutSession($sessionId);

        ChannelAccount::where('workspace_id', $workspaceId)
            ->where('channel', 'whatsapp')
            ->where('provider', 'qr_baileys')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp QR Account disconnected.',
        ]);
    }
}
