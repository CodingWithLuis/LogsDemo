<x-layouts::app :title="__('Edit Sale')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <a href="{{ route('sales.index') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                &larr; Back to sales
            </a>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white">Edit {{ $sale->invoice_number }}</h1>
        </div>

        @include('partials.flash')

        <form method="POST" action="{{ route('sales.update', $sale) }}" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            @csrf
            @method('PUT')

            @include('sales._form')

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                    class="rounded-lg bg-neutral-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                    Save changes
                </button>
                <a href="{{ route('sales.show', $sale) }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts::app>