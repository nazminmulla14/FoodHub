<?php

namespace App\Models;

use App\Enums\RestaurantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'image',
        'address',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => RestaurantStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopePubliclyVisible($query)
    {
        return $query
            ->where('is_active', true)
            ->where('status', RestaurantStatus::Active);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(RestaurantHour::class);
    }

    public function isOpen(?Carbon $at = null): bool
    {
        $at = ($at ?? now())->timezone(config('app.timezone'));
        $hours = $this->relationLoaded('hours') ? $this->hours : $this->hours()->get();

        if ($hours->isEmpty()) {
            return true;
        }

        $today = $hours->firstWhere('weekday', $at->dayOfWeek);

        if (! $today || $today->is_closed || ! $today->opens_at || ! $today->closes_at) {
            return false;
        }

        $open = $at->copy()->setTimeFromTimeString(substr((string) $today->opens_at, 0, 8));
        $close = $at->copy()->setTimeFromTimeString(substr((string) $today->closes_at, 0, 8));

        return $at->betweenIncluded($open, $close);
    }

    public function nextOpenLabel(): ?string
    {
        if ($this->isOpen()) {
            return null;
        }

        $at = now()->timezone(config('app.timezone'));
        $hours = $this->relationLoaded('hours') ? $this->hours : $this->hours()->get();

        for ($i = 0; $i < 8; $i++) {
            $day = $at->copy()->addDays($i);
            $row = $hours->firstWhere('weekday', $day->dayOfWeek);

            if (! $row || $row->is_closed || ! $row->opens_at) {
                continue;
            }

            $open = $day->copy()->setTimeFromTimeString(substr((string) $row->opens_at, 0, 8));

            if ($open->greaterThan($at)) {
                return $open->format('D H:i');
            }
        }

        return null;
    }
}
