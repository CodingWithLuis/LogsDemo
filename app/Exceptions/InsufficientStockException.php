<?php

namespace App\Exceptions;

use Throwable;

class InsufficientStockException extends \RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly int $requestedQuantity,
        public readonly int $availableStock,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message ?: 'Not enough stock available.', $code, $previous);
    }

    /**
     * Return structured data merged into the exception's log context.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'product_id' => $this->productId,
            'requested_quantity' => $this->requestedQuantity,
            'available_stock' => $this->availableStock,
        ];
    }
}
