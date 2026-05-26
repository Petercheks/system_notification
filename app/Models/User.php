<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'phone'])]
class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    public function subscribed(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_category_subscriptions')
            ->using(UserCategorySubscription::class)
            ->withTimestamps();
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'user_channels')
            ->using(UserChannel::class)
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
