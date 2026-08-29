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
