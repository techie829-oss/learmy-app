<?php

namespace App\Modules\Social\Services\Drivers;

use App\Modules\Social\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class InstagramSocialDriver implements SocialNetworkInterface
{
    public function network(): string
    {
        return 'instagram';
    }

    public function fetchAccountInfo(string $accessToken): array
    {
        $res = Http::get('https://graph.instagram.com/me', [
            'fields' => 'id,name,profile_picture_url',
            'access_token' => $accessToken,
        ])->json();

        return [
            'account_id' => $res['id'] ?? '',
            'name' => $res['name'] ?? '',
            'picture_url' => $res['profile_picture_url'] ?? null,
        ];
    }

    public function publish(SocialAccount $account, array $postData): string
    {
        $igUserId = $account->account_id;
        $token = $account->access_token;

        // Step 1: Create media container
        $containerPayload = ['caption' => $postData['body'] ?? '', 'access_token' => $token];
        $mediaUrls = array_values(array_filter($postData['media_urls'] ?? [], fn ($u) => $u !== null && $u !== ''));
        if (! empty($mediaUrls)) {
            $containerPayload['image_url'] = $mediaUrls[0];
        } else {
            throw new \RuntimeException('Instagram posts require at least one image.');
        }

        $graphUrl = config('all.meta.graph_url', 'https://graph.facebook.com/v20.0');
        $container = Http::post("{$graphUrl}/{$igUserId}/media", $containerPayload)->json();

        if (empty($container['id'])) {
            throw new \Exception('Failed to create media container: ' . json_encode($container));
        }

        $res = Http::post("{$graphUrl}/{$igUserId}/media_publish", [
            'creation_id'  => $container['id'],
            'access_token' => $token,
        ])->json();

        return $res['id'] ?? throw new \RuntimeException('Instagram publish failed: '.json_encode($res));
    }
}
