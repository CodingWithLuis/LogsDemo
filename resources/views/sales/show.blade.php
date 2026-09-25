<x-layouts::app :title="__('Sale Details')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('sales.index') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                    &larr; Back to sales
                </a>
                <h1 class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white">{{ $sale->invoice_number }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('sales.edit', $sale) }}"
                    class="inline-flex items-center rounded-lg bg-neutral-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                    Edit sale
                </a>
                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Delete this sale? Product stock will be restored.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/40">
                        Delete sale
                    </button>
                </form>
            </div>
        </div>

        @include('partials.flash')

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <h2 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Sale info</h2>
                <dl class="grid gap-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-neutral-500 dark:text-neutral-400">Invoice</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ $sale->invoice_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-neutral-500 dark:text-neutral-400">Employee</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ $sale->employee?->fullName() ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-neutral-500 dark:text-neutral-400">Date</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ $sale->sale_date->format('F j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-neutral-500 dark:text-neutral-400">Status</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($sale->status) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-neutral-500 dark:text-neutral-400">Created</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ $sale->created_at->format('M d, Y g:i A') }}</dd>
                    </div>
                    @if ($sale->notes)
                        <div class="flex justify-between gap-4">
                            <dt class="text-neutral-500 dark:text-neutral-400">Notes</dt>
                            <dd class="max-w-[60%] font-medium text-neutral-900 dark:text-white">{{ $sale->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <h2 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Line items</h2>
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead>
                        <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            <th class="pb-2">Product</th>
                            <th class="pb-2 text-right">Price</th>
                            <th class="pb-2 text-right">Qty</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach ($sale->details as $detail)
                            <tr class="text-sm">
                                <td class="py-2 font-medium text-neutral-900 dark:text-white">
                                    {{ $detail->product?->name ?? 'Unknown product' }}
                                    <span class="block text-xs font-normal text-neutral-400">{{ $detail->product?->sku }}</span>
                                </td>
                                <td class="py-2 text-right text-neutral-500 dark:text-neutral-400">${{ number_format($detail->unit_price, 2) }}</td>
                                <td class="py-2 text-right text-neutral-500 dark:text-neutral-400">{{ $detail->quantity }}</td>
                                <td class="py-2 text-right font-medium text-neutral-900 dark:text-white">${{ number_format($detail->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="text-sm">
                            <td colspan="3" class="pt-3 text-right font-semibold text-neutral-900 dark:text-white">Total</td>
                            <td class="pt-3 text-right font-semibold text-neutral-900 dark:text-white">${{ number_format($sale->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>