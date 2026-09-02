<?php

namespace App\Data;

final readonly class InventoryMovementData
{
    public function __construct(
        public int $inventoryId,
        public int $productId,
        public string $type,
        public int $quantity,
        public ?string $reason = null,
        public ?string $sourceLocation = null,
        public ?string $destinationLocation = null,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public array $metadata = [],
    ) {
    }
}
