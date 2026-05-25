<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'phone_number'])]
class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    public function subcribed(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_category_subcriptions')
            ->using(UserCategorySubcription::class)
            ->withTimestamps();
    }
}
