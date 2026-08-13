<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $types = [
            'comment' => [
                'commented on your post',
                'replied to your comment',
                'mentioned you in a comment',
            ],
            'like' => [
                'liked your post',
                'liked your comment',
                'loved your article',
            ],
            'follow' => [
                'started following you',
                'is now following your updates',
            ],
            'mention' => [
                'mentioned you in a post',
                'tagged you in a discussion',
                'referenced you in an article',
            ],
        ];

        $names = [
            'Sarah Johnson', 'Michael Chen', 'Emily Davis', 'James Wilson',
            'Jessica Brown', 'David Miller', 'Amanda Garcia', 'Robert Martinez',
            'Lisa Anderson', 'Christopher Lee',
        ];

        for ($i = 0; $i < 40; $i++) {

            $type = array_rand($types);

            Notification::create([
                'type' => $type,
                'message' => fake()->randomElement($types[$type]),
                'actor_name' => fake()->randomElement($names),
                'actor_avatar' => 'https://i.pravatar.cc/150?u=' . fake()->uuid(),
                'read_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-1 week', 'now') : null,
                'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ]);

        }

    }

}
