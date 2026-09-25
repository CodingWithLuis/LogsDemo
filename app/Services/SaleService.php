<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Handles the creation, update, and removal of sales along with their
 * line items, keeping product stock consistent.
 */
class SaleService
{
    private const LOW_STOCK_THRESHOLD = 10;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Sale
    {
        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'invoice_number' => $data['invoice_number'],
                'employee_id' => $data['employee_id'],
                'sale_date' => $data['sale_date'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->attachDetails($sale, $data['details']);

            Log::info('Sale created.', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => $sale->total,
                'line_items' => count($data['details']),
                'performed_by' => auth()->id() ?? null,
            ]);

            DB::commit();

            return $sale->load('details');
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Sale $sale, array $data): Sale
    {
        DB::beginTransaction();

        try {
            $this->restoreStock($sale);

            $sale->update([
                'invoice_number' => $data['invoice_number'],
                'employee_id' => $data['employee_id'],
                'sale_date' => $data['sale_date'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->details()->delete();

            $this->attachDetails($sale, $data['details']);

            Log::info('Sale updated.', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => $sale->total,
                'line_items' => count($data['details']),
                'performed_by' => auth()->id() ?? null,
            ]);

            DB::commit();

            return $sale->load('details');
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function remove(Sale $sale): void
    {
        DB::beginTransaction();

        try {
            $this->restoreStock($sale);

            $sale->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Create the sale's line items, decrement stock, and compute the total.
     *
     * @param  array<int, array<string, mixed>>  $details
     */
    private function attachDetails(Sale $sale, array $details): void
    {
        $total = 0;

        foreach ($details as $detail) {
            $product = Product::query()->lockForUpdate()->findOrFail((int) $detail['product_id']);

            if ($product->stock < $detail['quantity']) {
                throw new InsufficientStockException(
                    productId: $product->id,
                    requestedQuantity: $detail['quantity'],
                    availableStock: $product->stock,
                );
            }

            $lineTotal = round($product->price * $detail['quantity'], 2);
            $total = round($total + $lineTotal, 2);

            $sale->details()->create([
                'product_id' => $product->id,
                'quantity' => $detail['quantity'],
                'unit_price' => $product->price,
                'line_total' => $lineTotal,
            ]);

            $product->decrement('stock', $detail['quantity']);

            $this->logLowStockIfNeeded($product);
        }

        $sale->update(['total' => $total]);
    }

    private function restoreStock(Sale $sale): void
    {
        $sale->loadMissing('details');

        foreach ($sale->details as $detail) {
            Product::query()->whereKey($detail->product_id)->increment('stock', $detail->quantity);
        }
    }

    private function logLowStockIfNeeded(Product $product): void
    {
        if ($product->stock <= self::LOW_STOCK_THRESHOLD) {
            Log::warning('Product stock dropped to or below the low-stock threshold.', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'remaining_stock' => $product->stock,
                'threshold' => self::LOW_STOCK_THRESHOLD,
            ]);
        }
    }
}
