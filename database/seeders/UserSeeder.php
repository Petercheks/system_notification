<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $channels = Channel::all();

        User::factory(10)->create()->each(function (User $user) use ($categories, $channels) {
            $user->subcribed()->attach(
                $categories->random(rand(1, $categories->count()))->pluck('id')
            );

            $user->channels()->attach(
                $channels->random(rand(1, $channels->count()))->pluck('id')
            );
        });
    }
}
