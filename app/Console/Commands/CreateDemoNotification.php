<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('create-notification')]
#[Description('Create a demo notification for testing the notification center')]
class CreateDemoNotification extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
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

        $type = array_rand($types);

        $notification = Notification::create([
            'type' => $type,
            'message' => fake()->randomElement($types[$type]),
            'actor_name' => fake()->randomElement($names),
            'actor_avatar' => 'https://i.pravatar.cc/150?u=' . fake()->uuid(),
            'read_at' => null,
        ]);

        $this->info("Created {$type} notification from {$notification->actor_name}");

        return self::SUCCESS;

    }
}
