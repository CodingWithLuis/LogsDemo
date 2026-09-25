<?php

namespace App\Http\Requests\Concerns;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

trait ValidatesSaleDetails
{
    /**
     * Cross-field stock validation for a sale's line items.
     *
     * @return array<int, Closure|ValidationRule>
     */
    protected function saleDetailValidations(): array
    {
        return [
            function (Validator $validator): void {
                $details = $this->input('details');

                if (! is_array($details)) {
                    return;
                }

                foreach ($details as $index => $detail) {
                    if ($validator->errors()->has("details.$index.quantity") || $validator->errors()->has("details.$index.product_id")) {
                        continue;
                    }

                    $productId = isset($detail['product_id']) ? (int) $detail['product_id'] : null;

                    if ($productId === null) {
                        continue;
                    }

                    $product = Product::find($productId);

                    if ($product === null) {
                        continue;
                    }

                    if ($product->stock < $detail['quantity']) {
                        $validator->errors()->add(
                            "details.$index.quantity",
                            "Only {$product->stock} units of \"{$product->name}\" are available.",
                        );
                    }

                    if ($product->stock <= 10) {
                        Log::warning('Low stock warning during sale validation.', [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'remaining_stock' => $product->stock,
                            'date' => now(),
                        ]);
                    }
                }
            },
        ];
    }
}
