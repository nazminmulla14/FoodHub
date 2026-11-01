<div>
    @if ($showReplacePrompt)
        <div class="alert alert-warning py-2 px-2 small mb-2">
            <p class="mb-2">{{ $replaceMessage }}</p>
            <div class="d-flex gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-warning" wire:click="replaceCartAndAdd">Replace cart</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="cancelReplace">Keep cart</button>
            </div>
        </div>
    @endif

    @if ($error)
        <div class="alert alert-danger py-1 px-2 small mb-2">{{ $error }}</div>
    @endif

    @foreach ($optionGroups as $group)
        <label class="form-label small mb-1">{{ $group->name }}@if($group->is_required) * @endif</label>
        @if ($group->max_select <= 1)
            <select class="form-select form-select-sm mb-2" wire:model="selectedOptionIds.{{ $group->id }}">
                <option value="">Choose</option>
                @foreach ($group->options as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}@if((float) $option->price_delta > 0) (+₹{{ number_format((float) $option->price_delta, 2) }})@endif</option>
                @endforeach
            </select>
        @else
            @foreach ($group->options as $option)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{ $option->id }}" wire:model="selectedOptionIds.{{ $group->id }}" id="opt-{{ $option->id }}">
                    <label class="form-check-label small" for="opt-{{ $option->id }}">{{ $option->name }}</label>
                </div>
            @endforeach
        @endif
    @endforeach

    <button type="button" class="btn btn-sm btn-primary" wire:click="addToCart" wire:loading.attr="disabled" @disabled($disabled)>
        <span wire:loading.remove wire:target="addToCart,replaceCartAndAdd">{{ $disabled ? 'Unavailable' : 'Add' }}</span>
        <span wire:loading wire:target="addToCart,replaceCartAndAdd">...</span>
    </button>
</div>
