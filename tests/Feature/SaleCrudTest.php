<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_managing_sales(): void
    {
        $this->get(route('sales.index'))->assertRedirect(route('login'));
        $this->get(route('sales.create'))->assertRedirect(route('login'));
        $this->post(route('sales.store'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_list_sales(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Lina', 'last_name' => 'Gomez']);
        $product = Product::factory()->create(['price' => 25.00, 'stock' => 10]);
        $sale = $this->createSale($employee, $product, 1);

        $this->actingAs($this->admin())
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee($sale->invoice_number)
            ->assertSee('Lina Gomez');
    }

    public function test_sale_cannot_be_created_without_line_items(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('sales.store'), $this->salePayload($employee, details: []))
            ->assertSessionHasErrors('details');
    }

    public function test_sale_cannot_be_created_when_quantity_exceeds_stock(): void
    {
        $employee = Employee::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        $this->actingAs($this->admin())
            ->post(route('sales.store'), $this->salePayload($employee, details: [
                ['product_id' => $product->id, 'quantity' => 5],
            ]))
            ->assertSessionHasErrors('details.0.quantity');
    }

    public function test_authenticated_users_can_create_a_sale_and_decrement_stock(): void
    {
        $employee = Employee::factory()->create();
        $product = Product::factory()->create(['price' => 100.00, 'stock' => 5]);

        $this->actingAs($this->admin())
            ->post(route('sales.store'), $this->salePayload($employee, details: [
                ['product_id' => $product->id, 'quantity' => 2],
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sales', [
            'invoice_number' => 'INV-1000',
            'employee_id' => $employee->id,
            'total' => '200.00',
        ]);

        $this->assertDatabaseHas('sale_details', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '100.00',
            'line_total' => '200.00',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_authenticated_users_can_delete_a_sale_and_restore_stock(): void
    {
        $employee = Employee::factory()->create();
        $product = Product::factory()->create(['price' => 10.00, 'stock' => 5]);
        $sale = $this->createSale($employee, $product, 2);

        $this->actingAs($this->admin())
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
    }

    private function salePayload(Employee $employee, string $invoice = 'INV-1000', array $details = []): array
    {
        return [
            'invoice_number' => $invoice,
            'employee_id' => $employee->id,
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'notes' => null,
            'details' => $details,
        ];
    }

    private function createSale(Employee $employee, Product $product, int $quantity = 1): Sale
    {
        $unitPrice = (float) $product->price;

        $sale = Sale::create([
            'invoice_number' => 'INV-'.random_int(100, 999),
            'employee_id' => $employee->id,
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'total' => round($unitPrice * $quantity, 2),
        ]);

        $sale->details()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'line_total' => round($unitPrice * $quantity, 2),
        ]);

        $product->decrement('stock', $quantity);

        return $sale;
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['name' => 'Admin', 'slug' => 'admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
