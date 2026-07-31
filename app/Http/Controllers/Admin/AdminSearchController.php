<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $lower = strtolower($query);
        $results = [];

        // ── Navigation pages ────────────────────────────────────────────────
        $navItems = [
            ['label' => 'Dashboard',            'href' => route('admin.dashboard'),            'icon' => 'LayoutDashboard'],
            ['label' => 'Admin Settings',        'href' => route('admin.settings.index'),       'icon' => 'Settings'],
            ['label' => 'Integrations',          'href' => route('admin.integrations.index'),   'icon' => 'Plug'],
            ['label' => 'Email System',          'href' => route('admin.email-system.index'),   'icon' => 'FileText'],
        ];

        foreach ($navItems as $item) {
            if (str_contains(strtolower($item['label']), $lower)) {
                $results[] = array_merge($item, ['type' => 'page']);
            }
        }

        // ── Users ────────────────────────────────────────────────────────────
        User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(function (User $user) use (&$results) {
                $results[] = [
                    'type' => 'user',
                    'label' => $user->name,
                    'sub' => $user->email,
                    'href' => '#',
                    'icon' => 'User',
                ];
            });

        return response()->json(['results' => array_slice($results, 0, 15)]);
    }
}
