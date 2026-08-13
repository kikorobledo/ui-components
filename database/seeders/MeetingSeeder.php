<?php

namespace Database\Seeders;

use App\Models\Meeting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Meeting::create([
            'title' => 'Sprint Plannig',
            'date' => now()->addDay(),
            'notes' => "- Review last sprint performance\n- Set goals for upcoming sprint\n- Assing tasks to teams members",
        ]);

        $this->command->info("Sample meeting created!");

    }
}
