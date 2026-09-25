<x-layouts::app :title="__('Sales')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">Sales</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Invoices created by employees.</p>
            </div>

            <a href="{{ route('sales.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-neutral-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                New sale
            </a>
        </div>

        @include('partials.flash')

        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-900">
                    @forelse ($sales as $sale)
                        <tr class="text-sm">
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-white">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $sale->employee?->fullName() ?? '—' }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $sale->sale_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-white">${{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if ($sale->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif ($sale->status === 'pending') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                    @else bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 @endif">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('sales.show', $sale) }}" class="font-medium text-neutral-700 hover:underline dark:text-neutral-200">View</a>
                                    <a href="{{ route('sales.edit', $sale) }}" class="font-medium text-neutral-700 hover:underline dark:text-neutral-200">Edit</a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Delete this sale? Product stock will be restored.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                No sales yet. Create the first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $sales->links() }}</div>
    </div>
</x-layouts::app>