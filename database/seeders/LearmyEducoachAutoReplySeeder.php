<?php

namespace Database\Seeders;

use App\Modules\Whatsapp\Models\WhatsappAutoReply;
use Illuminate\Database\Seeder;

class LearmyEducoachAutoReplySeeder extends Seeder
{
    public function run(): void
    {
        $workspaceId = 3;

        $rules = [
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'welcome',
                'match_mode' => 'contains',
                'keywords' => [],
                'response_kind' => 'text',
                'payload_json' => ['text' => "Welcome to Learmy Educoach Institute! 🎓🎵\nWe offer Premium Music & Academic Coaching in Bengaluru.\n\nType a keyword to get quick details:\n• *courses* - Music & Academic programs\n• *music* - Piano, Guitar, Violin, Drums\n• *academics* - LKG to Grade 12 Tuitions\n• *chess* - Chess & Brain Skills\n• *address* - Our Location & Contact\n• *enroll* - Book a demo class"],
                'enabled' => true,
                'priority' => 1,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['course', 'courses', 'program', 'class', 'classes', 'subject'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "📚 *Learmy Educoach Programs*\n\n1️⃣ *Music & Arts*: Piano, Keyboard, Guitar, Violin, Drums, Art & Craft, Karate, Yoga\n2️⃣ *Academic Tuitions*: LKG to Grade 12 (Science, Math, Physics, Chemistry, Biology)\n3️⃣ *Brain Development*: Chess Coaching for all age groups\n\nReply with *music*, *academics*, or *chess* for specific details!"],
                'enabled' => true,
                'priority' => 2,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['music', 'guitar', 'piano', 'violin', 'drum', 'drums', 'keyboard', 'singing'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "🎸 *Music & Performing Arts at Learmy*\n\nWe provide professional training by certified musicians:\n• Piano & Keyboard\n• Acoustic & Electric Guitar\n• Violin & Drums\n• Singing & Performing Arts\n\n✨ Small batch sizes for individual attention & international certification support!\nReply *enroll* to book a free trial class."],
                'enabled' => true,
                'priority' => 3,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['academic', 'tuition', 'science', 'math', 'physics', 'chemistry', 'biology', 'grade', 'school'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "🔬 *Academic Coaching at Learmy*\n\n• Classes for LKG to Grade 12 (All Boards)\n• Expert faculty led by Pranshi Ma'am (Science & Physics Specialist)\n• Hands-on practical experiments & conceptual clarity\n• Board Exam Preparation & Regular Tests\n\nReply *enroll* to register your child!"],
                'enabled' => true,
                'priority' => 4,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['chess', 'art', 'craft', 'drawing', 'painting'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "♟️ *Chess & Creative Skill Classes*\n\n• *Chess*: Strategic thinking, focus & tactics for beginners & competitive players\n• *Art & Craft*: Painting, drawing, DIY & fine motor skill development\n\nSuitable for all age groups! Reply *enroll* to join."],
                'enabled' => true,
                'priority' => 5,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['address', 'location', 'where', 'contact', 'phone', 'number', 'map'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "📍 *Learmy Educoach Institute*\nShop No-1, Vasupradha Residency, Kaithola Main Road, Nagondanahalli, Bengaluru - 560066\n\n📞 Phone/WhatsApp: +91 74834 24001\n📧 Email: learmymusic@gmail.com\n🌐 Website: https://learmyeducoach.com"],
                'enabled' => true,
                'priority' => 6,
            ],
            [
                'workspace_id' => $workspaceId,
                'trigger_type' => 'keyword',
                'match_mode' => 'contains',
                'keywords' => ['enroll', 'join', 'admission', 'demo', 'register', 'fee', 'price'],
                'response_kind' => 'text',
                'payload_json' => ['text' => "🎟️ *Enroll at Learmy Educoach*\n\nThank you for your interest! Please share:\n1. Student's Name\n2. Grade / Course interested in\n3. Preferred Time Slot\n\nOur team (+91 74834 24001) will contact you shortly to schedule your demo class!"],
                'enabled' => true,
                'priority' => 7,
            ],
        ];

        foreach ($rules as $rule) {
            WhatsappAutoReply::updateOrCreate(
                ['workspace_id' => $rule['workspace_id'], 'priority' => $rule['priority']],
                $rule
            );
        }
    }
}
