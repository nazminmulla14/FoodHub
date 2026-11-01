<?php

namespace App\Livewire\Admin;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Restaurant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class CouponsIndex extends Component
{
    use WithPagination;

    public string $code = '';

    public string $type = 'percent';

    public string $value = '';

    public string $restaurant_id = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public string $usage_limit = '';

    public bool $is_active = true;

    public function save(): void
    {
        $this->authorize('create', Coupon::class);

        $this->code = strtoupper(trim($this->code));

        $validated = $this->validate([
            'code' => ['required', 'string', 'max:40', 'unique:coupons,code'],
            'type' => ['required', 'in:percent,flat'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            $this->addError('value', 'Percent discounts cannot be more than 100.');

            return;
        }

        Coupon::create([
            'code' => strtoupper(trim($validated['code'])),
            'type' => CouponType::from($validated['type']),
            'value' => $validated['value'],
            'restaurant_id' => $validated['restaurant_id'] ?: null,
            'starts_at' => $validated['starts_at'] ?: null,
            'ends_at' => $validated['ends_at'] ?: null,
            'usage_limit' => $validated['usage_limit'] !== '' ? $validated['usage_limit'] : null,
            'is_active' => $validated['is_active'],
        ]);

        $this->reset(['code', 'value', 'restaurant_id', 'starts_at', 'ends_at', 'usage_limit']);
        $this->type = 'percent';
        $this->is_active = true;
        session()->flash('admin_success', 'Coupon created.');
    }

    public function toggle(int $couponId): void
    {
        $coupon = Coupon::query()->findOrFail($couponId);
        $this->authorize('update', $coupon);
        $coupon->is_active = ! $coupon->is_active;
        $coupon->save();
    }

    public function render()
    {
        $this->authorize('viewAny', Coupon::class);

        return view('livewire.admin.coupons-index', [
            'coupons' => Coupon::query()->with('restaurant')->latest()->paginate(15),
            'restaurants' => Restaurant::query()->orderBy('name')->get(),
        ]);
    }
}
