<?php

namespace App\Exceptions;

use Exception;

class CartRestaurantConflictException extends Exception
{
    public function __construct(
        public readonly string $currentRestaurantName,
        public readonly string $newRestaurantName,
    ) {
        parent::__construct(
            "Your cart has items from {$currentRestaurantName}. Replace the cart to add items from {$newRestaurantName}?"
        );
    }
}
