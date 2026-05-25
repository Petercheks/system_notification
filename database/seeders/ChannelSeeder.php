<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseChannels = [
            'Email',
            'SMS',
            'Push Notification',
        ];

        foreach ($baseChannels as $channel) {
            Channel::create([
                'name' => $channel,
                'slug' => Str::slug($channel),
            ]);
        }
    }
}
