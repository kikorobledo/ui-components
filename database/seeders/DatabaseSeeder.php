<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\GroupSeeder;
use Database\Seeders\MeetingSeeder;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\TaskSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(MeetingSeeder::class);

        $this->call(UserSettingSeeder::class);

        $this->call(TagSeeder::class);

        $this->call(PostSeeder::class);

        $this->call(NotificationSeeder::class);

        /* $this->call(TaskSeeder::class); */

        $this->call(GroupSeeder::class);

    }
}
