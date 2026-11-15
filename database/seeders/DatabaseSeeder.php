<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Module 4 (Lai Chee Kit): TEMPORARY test accounts until Module 1's users are ready
        $student = User::factory()->create(['name' => 'Sam Student', 'email' => 'student@example.com', 'role' => 'student']);
        $staff = User::factory()->create(['name' => 'Stella Staff', 'email' => 'staff@example.com', 'role' => 'staff']);
        User::factory()->create(['name' => 'Adam Admin', 'email' => 'admin@example.com', 'role' => 'admin']);

        // Module 4 (Lai Chee Kit): sample tickets
        $student->tickets()->create([
            'category' => 'Fault',
            'priority' => 'High',
            'equipment_id' => 2,
            'title' => 'Projector shows no image',
            'description' => 'The projector powers on but the screen stays blank, even after changing the HDMI cable.',
        ]);
        $student->tickets()->create([
            'category' => 'Usage Problem',
            'priority' => 'Low',
            'title' => 'How do I extend my borrowing period?',
            'description' => 'I need the camera for two more days for my assignment shooting.',
        ]);
        $staff->tickets()->create([
            'category' => 'General Feedback',
            'priority' => 'Medium',
            'title' => 'Please add more HDMI adapters',
            'description' => 'Lab 3 often runs out of USB-C to HDMI adapters during presentation weeks.',
        ]);
    }
}
