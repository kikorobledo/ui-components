<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Phase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $phases = ['Todo', 'In progress', 'Done'];

        $colors = ['blue', 'yellow', 'green', 'amber', 'red', 'orange', 'indigo', 'tail', 'cyan', 'gray', 'purple', 'pink'];

        $group = Group::create([
            'name' => 'Project 1',
            'status' => 'In progress',
            'area' => 'Direction',
            'created_by' => 1
        ]);

        foreach ($phases as $key => $phase) {

            Phase::create([
                'name' => $phase,
                'color' => $colors[$key],
                'order' => $key + 1,
                'group_id' => $group->id
            ]);

        }

    }
}
