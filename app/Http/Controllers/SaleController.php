<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $sales = Sale::query()
            ->with('employee')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('sales.index', ['sales' => $sales]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('sales.create', [
            'sale' => null,
            'employees' => Employee::query()->orderBy('first_name')->orderBy('last_name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request, SaleService $service): RedirectResponse
    {
        try {
            $sale = $service->create($request->validated());

            return redirect()->route('sales.show', $sale)
                ->with('success', "La venta {$sale->invoice_number} se ha creado correctamente.");
        } catch (InsufficientStockException $e) {
            Log::error('Insufficient stock when creating sale.', [
                'invoice_number' => $request->input('invoice_number'),
                ...$e->context(),
            ]);

            return redirect()->route('sales.create')
                ->withInput()
                ->with('error', 'Uno de los productos ya no tiene suficiente stock. Por favor, ajusta las cantidades.');
        } catch (Throwable $e) {
            Log::error('Failed to create sale.', [
                'error' => $e->getMessage(),
                'invoice_number' => $request->input('invoice_number'),
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('sales.create')
                ->withInput()
                ->with('error', 'Algo salió mal al crear la venta. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['employee', 'details.product']);

        return view('sales.show', ['sale' => $sale]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale): View
    {
        $sale->load('details');

        return view('sales.edit', [
            'sale' => $sale,
            'employees' => Employee::query()->orderBy('first_name')->orderBy('last_name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleRequest $request, Sale $sale, SaleService $service): RedirectResponse
    {
        try {
            $service->update($sale, $request->validated());

            return redirect()->route('sales.show', $sale)
                ->with('success', "La venta {$sale->invoice_number} se ha actualizado correctamente.");
        } catch (InsufficientStockException $e) {
            Log::error('Insufficient stock when updating sale.', [
                'sale_id' => $sale->id,
                ...$e->context(),
            ]);

            return redirect()->route('sales.edit', $sale)
                ->withInput()
                ->with('error', 'Uno de los productos ya no tiene suficiente stock. Por favor, ajusta las cantidades.');
        } catch (Throwable $e) {
            Log::error('Failed to update sale.', [
                'error' => $e->getMessage(),
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('sales.edit', $sale)
                ->withInput()
                ->with('error', 'Algo salió mal al actualizar la venta. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale, SaleService $service): RedirectResponse
    {
        try {
            $invoiceNumber = $sale->invoice_number;

            $service->remove($sale);

            Log::info('Sale deleted.', [
                'sale_id' => $sale->id,
                'invoice_number' => $invoiceNumber,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('sales.index')
                ->with('success', "La venta {$invoiceNumber} se ha eliminado correctamente.");
        } catch (Throwable $e) {
            Log::error('Failed to delete sale.', [
                'error' => $e->getMessage(),
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'performed_by' => auth()->id() ?? null,
            ]);

            return back()->with('error', 'Algo salió mal al eliminar la venta. Por favor, inténtalo de nuevo.');
        }
    }
}
