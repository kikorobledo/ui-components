<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $tasks = [
            ['title' => 'Finalize guest speaker list', 'position' => 0],
            ['title' => 'Order table centerpieces', 'position' => 1],
            ['title' => 'Arrange parking signage', 'position' => 2],
            ['title' => 'Confirm catering menu with vendor', 'position' => 0],
            ['title' => 'Design event program booklet', 'position' => 1],
            ['title' => 'Send invitations to attendees', 'position' => 2],
            ['title' => 'Book venue and sign contract', 'position' => 0],
            ['title' => 'Hire photographer for the day', 'position' => 1],
        ];

        foreach ($tasks as $task) {

            Task::create($task);

        }

    }
}
