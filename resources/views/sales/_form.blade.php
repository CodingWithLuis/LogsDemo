@php
    $editing = isset($sale) && $sale !== null;

    $detailRows = collect(old('details') ?? ($sale->details ?? [[
        'product_id' => null,
        'quantity' => 1,
    ]]))
        ->map(fn ($detail) => $detail instanceof \App\Models\SaleDetail
            ? ['product_id' => $detail->product_id, 'quantity' => $detail->quantity]
            : $detail)
        ->values()
        ->all();
@endphp

<div class="grid gap-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="invoice_number" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Invoice number</label>
            <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $sale->invoice_number ?? '') }}" required
                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
            @error('invoice_number')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="employee_id" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Employee</label>
            <select id="employee_id" name="employee_id" required
                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                <option value="">Select an employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id', $sale->employee_id ?? null) == $employee->id)>
                        {{ $employee->fullName() }} &mdash; {{ $employee->position }}
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sale_date" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Sale date</label>
            <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date', $sale?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required
                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
            @error('sale_date')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Status</label>
            <select id="status" name="status" required
                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                @foreach (['pending', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $sale->status ?? 'completed') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="notes" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Notes</label>
        <textarea id="notes" name="notes" rows="2"
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">{{ old('notes', $sale->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Line items</label>
            <button type="button" id="add-line"
                class="rounded-lg border border-neutral-300 px-3 py-1.5 text-xs font-medium text-neutral-700 transition hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                + Add line
            </button>
        </div>

        <div class="overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        <th class="px-3 py-2">Product</th>
                        <th class="px-3 py-2">Quantity</th>
                        <th class="px-3 py-2 text-right">Line total</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody id="sale-lines" class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-900">
                    @foreach ($detailRows as $detailIndex => $detail)
                        <tr class="sale-line text-sm">
                            <td class="px-3 py-2">
                                <select name="details[{{ $detailIndex }}][product_id]" class="line-product w-full rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Select product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ number_format($product->price, 2) }}"
                                            @selected(old("details.$detailIndex.product_id", $detail['product_id']) == $product->id)>
                                            {{ $product->name }} &mdash; ${{ number_format($product->price, 2) }} ({{ $product->stock }} in stock)
                                        </option>
                                    @endforeach
                                </select>
                                @error("details.$detailIndex.product_id")
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="1" name="details[{{ $detailIndex }}][quantity]"
                                    value="{{ old("details.$detailIndex.quantity", $detail['quantity']) }}"
                                    class="line-quantity w-24 rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                @error("details.$detailIndex.quantity")
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="line-total px-3 py-2 text-right text-sm font-medium text-neutral-900 dark:text-white">&mdash;</td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" class="remove-line text-neutral-400 transition hover:text-red-500" title="Remove line">&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                    <tr class="text-sm">
                        <td colspan="2" class="px-3 py-2 text-right font-medium text-neutral-700 dark:text-neutral-200">Total</td>
                        <td id="sale-total" class="px-3 py-2 text-right font-semibold text-neutral-900 dark:text-white">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @error('details')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<template id="sale-line-template">
    <tr class="sale-line text-sm">
        <td class="px-3 py-2">
            <select name="details[__INDEX__][product_id]" class="line-product w-full rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                <option value="">Select product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ number_format($product->price, 2) }}">
                        {{ $product->name }} &mdash; ${{ number_format($product->price, 2) }} ({{ $product->stock }} in stock)
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2">
            <input type="number" min="1" name="details[__INDEX__][quantity]" value="1"
                class="line-quantity w-24 rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
        </td>
        <td class="line-total px-3 py-2 text-right text-sm font-medium text-neutral-900 dark:text-white">&mdash;</td>
        <td class="px-3 py-2 text-right">
            <button type="button" class="remove-line text-neutral-400 transition hover:text-red-500" title="Remove line">&times;</button>
        </td>
    </tr>
</template>

<script>
    (function () {
        const linesBody = document.getElementById('sale-lines');
        const template = document.getElementById('sale-line-template');
        const addButton = document.getElementById('add-line');
        const totalCell = document.getElementById('sale-total');

        function reindex() {
            linesBody.querySelectorAll('tr.sale-line').forEach((row, index) => {
                row.querySelectorAll('select, input').forEach((el) => {
                    el.name = el.name.replace(/details\[\d+\]/, 'details[' + index + ']');
                });
            });
        }

        function productPrice(productId) {
            const option = Array.from(linesBody.querySelectorAll('.line-product option'))
                .find((opt) => opt.value === String(productId));

            return option ? parseFloat(option.dataset.price) : 0;
        }

        function computeTotals() {
            let total = 0;

            linesBody.querySelectorAll('tr.sale-line').forEach((row) => {
                const productId = row.querySelector('.line-product').value;
                const quantity = parseInt(row.querySelector('.line-quantity').value || '0', 10);
                const price = productId ? productPrice(productId) : 0;
                const lineTotal = price * quantity;

                row.querySelector('.line-total').textContent = (price && quantity)
                    ? '$' + lineTotal.toFixed(2)
                    : '\u2014';

                total += lineTotal;
            });

            totalCell.textContent = '$' + total.toFixed(2);
        }

        function addLine() {
            linesBody.appendChild(template.content.cloneNode(true));
            reindex();
            computeTotals();
        }

        linesBody.addEventListener('change', (event) => {
            if (event.target.classList.contains('line-product') || event.target.classList.contains('line-quantity')) {
                computeTotals();
            }
        });

        linesBody.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-line')) {
                event.target.closest('tr').remove();
                reindex();
                computeTotals();
            }
        });

        addButton.addEventListener('click', addLine);

        computeTotals();
    })();
</script>