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
            // 1. Class Scheduled Trigger
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

            // 2. Morning Reminder Trigger (08:00 AM)
            [
                'name'     => 'class_morning_reminder',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Good Morning! Class Reminder',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Good morning {{1}}!\n\nThis is a quick reminder for your class \"{{2}}\" scheduled for today at {{3}}.\n\nMeeting Link:\n{{4}}\n\nHave a great learning session today!",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Physics Ch-4', '26 Aug, 10:00 AM', 'https://meet.google.com/abc-defg-hij'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Morning Schedule',
                    ],
                ],
            ],

            // 3. 15 Minutes Before Class Trigger
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

            // 4. Class Started Trigger (Live Now)
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

            // 5. Course Inquiry Follow-Up (Marketing)
            [
                'name'     => 'course_inquiry_followup',
                'language' => 'en',
                'category' => 'MARKETING',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Welcome to Learmy Academy! 🎓',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Hi {{1}},\n\nThank you for inquiring about our {{2}} course at Learmy!\n\nHere is what you get in this program:\n• Live Interactive Classes & Recorded Lectures\n• Hands-on Practical Projects\n• 1-on-1 Mentorship & Doubt Resolution\n• Verified Certificate of Completion\n\nWould you like to schedule a free 1-on-1 counseling session with our lead mentor today?\n\nBest regards,\nLearmy Team",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Full-Stack Web Development'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Reply STOP to unsubscribe',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Book Free Demo'],
                            ['type' => 'QUICK_REPLY', 'text' => 'View Syllabus'],
                            ['type' => 'QUICK_REPLY', 'text' => 'Talk to Counselor'],
                        ],
                    ],
                ],
            ],

            // 6. Special Discount Offer (Marketing)
            [
                'name'     => 'special_course_offer',
                'language' => 'en',
                'category' => 'MARKETING',
                'status'   => 'APPROVED',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Exclusive {{1}}% Discount Alert!',
                        'example' => [
                            'header_text' => ['25'],
                        ],
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Hi {{1}},\n\nSpecial news! You have unlocked an exclusive {{2}}% discount on our top-rated {{3}} certification course.\n\nUse promo code: *{{4}}* at checkout.\n\n⏰ *Note: This offer is valid for the next {{5}} hours only.* Upgrading your skills has never been easier!",
                        'example' => [
                            'body_text' => [
                                ['Rahul', '25', 'Data Science & AI', 'LEARMY25', '24'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Limited Time Offer • Terms Apply',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type'    => 'URL',
                                'text'    => 'Claim Offer Now',
                                'url'     => 'https://learmy.solidrix.com/courses/{{1}}',
                                'example' => ['data-science-ai'],
                            ],
                            ['type' => 'QUICK_REPLY', 'text' => 'Ask a Question'],
                        ],
                    ],
                ],
            // 9. Offline Class Scheduled Trigger
            [
                'name'     => 'offline_class_on_create',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'PENDING',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Offline Class Scheduled',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Namaste {{1}} ji!\n\nAapki offline class schedule ho gayi hai:\n\n📚 Class: {{2}}\n🕒 Time: {{3}}\n📍 Venue: {{4}}\n\nPlease calendar mein date mark kar lein aur time par classroom aayein! 🗓️",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Algebra Ch-3', '26 Aug, 10:00 AM', 'Learmy Institute Room 204'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Education Platform',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Attend Karunga'],
                        ],
                    ],
                ],
            ],

            // 10. Offline Class Morning Reminder
            [
                'name'     => 'offline_class_morning_reminder',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'PENDING',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Today Offline Class Reminder',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Namaste {{1}} ji!\n\nAaj aapki offline class scheduled hai:\n\n📚 Class: {{2}}\n🕒 Time: {{3}}\n📍 Venue: {{4}}\n\nTime par tayyar ho kar center pahunchein! 🎯",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Algebra Ch-3', 'Today 04:00 PM', 'Learmy Institute Room 204'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Education Platform',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Ready Hoon'],
                        ],
                    ],
                ],
            ],

            // 11. Offline Class 15 Mins Before
            [
                'name'     => 'offline_class_before_15m',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'PENDING',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Offline Class in 15 Mins',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Namaste {{1}} ji!\n\nAapki offline class 15 minute mein shuru hone wali hai:\n\n📚 Class: {{2}}\n🕒 Time: {{3}}\n📍 Venue: {{4}}\n\nAbhi classroom mein seat le lein! ⚡",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Algebra Ch-3', '04:00 PM', 'Learmy Institute Room 204'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Education Platform',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'On My Way'],
                        ],
                    ],
                ],
            ],

            // 12. Offline Class On Start
            [
                'name'     => 'offline_class_on_start',
                'language' => 'en',
                'category' => 'UTILITY',
                'status'   => 'PENDING',
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Offline Class Has Started',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Namaste {{1}} ji!\n\nAapki offline class abhi shuru ho chuki hai:\n\n📚 Class: {{2}}\n📍 Venue: {{4}}\n\nLate na karein — abhi classroom mein aayein! 🚀",
                        'example' => [
                            'body_text' => [
                                ['Rahul', 'Algebra Ch-3', 'Learmy Institute Room 204'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Learmy Education Platform',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Present'],
                        ],
                    ],
                ],
            ],
        ];

        $wabaId = '1414388437341356';

        foreach ($workspaceIds as $wsId) {
            $client = \App\Modules\Whatsapp\Services\CloudApiClient::resolveForWorkspace($wsId);

            foreach ($templates as $tplData) {
                WhatsappTemplate::updateOrCreate(
                    [
                        'workspace_id' => $wsId,
                        'name'         => $tplData['name'],
                    ],
                    [
                        'waba_id'     => "workspace_{$wsId}_qr",
                        'category'   => $tplData['category'],
                        'language'   => $tplData['language'] ?? 'en',
                        'status'     => $tplData['status'] ?? 'PENDING',
                        'components' => $tplData['components'],
                    ]
                );

                if ($client) {
                    try {
                        $metaTpl = [
                            'name'       => $tplData['name'],
                            'category'   => $tplData['category'],
                            'language'   => $tplData['language'] ?? 'en',
                            'components' => $tplData['components'],
                        ];
                        $resp = $client->submitTemplate($wabaId, $metaTpl);
                        \Log::info("Meta template submitted from seeder: {$tplData['name']}", ['status' => $resp->status(), 'body' => $resp->body()]);
                    } catch (\Throwable $e) {
                        \Log::warning("Meta template submission failed for {$tplData['name']}", ['error' => $e->getMessage()]);
                    }
                }
            }
        }
    }
}
