<?php

namespace Database\Seeders;

use App\Models\UserSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserSetting::create([
            'name' => 'John Doe',
            'theme' => 'light',
            'language' => 'english',
        ]);

        $this->command->info('User settings created!');
    }
}
