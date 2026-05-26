<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('message_id')->constrained('messages');
            $table->string('category_slug', 20)->nullable();
            $table->string('channel_slug', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status', 'notifications_status_index');
            $table->index('category_slug', 'notifications_category_slug_index');
            $table->index('channel_slug', 'notifications_channel_slug_index');
            $table->index('created_at', 'notifications_created_at_index');

            $table->unique(
                ['user_id', 'message_id', 'channel_slug'],
                'notifications_user_message_channel_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
