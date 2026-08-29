<?php

namespace Database\Seeders;

use App\Models\Workspace;
use App\Modules\Whatsapp\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class LearmyEnglishWhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $workspaces = Workspace::all();

        if ($workspaces->isEmpty()) {
            $workspaceIds = [1];
        } else {
            $workspaceIds = $workspaces->pluck('id')->toArray();
        }

        $templates = [
            [
                'name'     => 'class_on_create_reminder',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Class Scheduled Successfully',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Hi {{1}},\n\nYour class \"{{2}}\" has been successfully scheduled.\n\nTime: {{3}}\nMeeting Link: {{4}}\n\nPlease make sure to join on time. See you in class!",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Physics Ch-4', '26 Aug, 10:00 AM', 'https://meet.google.com/abc-defg-hij'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Live Class',
                    ],
                ],
            ],
            [
                'name'     => 'class_before_15m_reminder',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Class Starts in 15 Minutes',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Hi {{1}},\n\nReminder: Your class \"{{2}}\" starts in 15 minutes at {{3}}.\n\nClick here to join the live room:\n{{4}}\n\nPlease join on time. See you inside!",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Physics Ch-4', '26 Aug, 10:00 AM', 'https://meet.google.com/abc-defg-hij'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Live Class',
                    ],
                ],
            ],
            [
                'name'     => 'class_on_start_reminder',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Class Has Started',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Hi {{1}},\n\nYour live class \"{{2}}\" scheduled for {{3}} has just started!\n\nJoin live immediately using the link below:\n{{4}}",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Physics Ch-4', '26 Aug, 10:00 AM', 'https://meet.google.com/abc-defg-hij'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Live Class',
                    ],
                ],
            ],
        ];

        foreach ($workspaceIds as $wsId) {
            foreach ($templates as $tplData) {
                WhatsappTemplate::updateOrCreate(
                    [
                        'workspace_id' => $wsId,
                        'name'         => $tplData['name'],
                        'language'     => $tplData['language'],
                    ],
                    [
                        'waba_id'     => "workspace_{$wsId}_qr",
                        'category'   => $tplData['category'],
                        'status'     => $tplData['status'],
                        'components' => $tplData['components'],
                    ]
                );
            }
        }
    }
}
