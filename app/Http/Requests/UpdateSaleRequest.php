<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSaleDetails;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
{
    use ValidatesSaleDetails;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('sales', 'invoice_number')->ignore($this->route('sale'))],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'sale_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get additional validation after the base rules have passed.
     *
     * @return array<int, Closure|ValidationRule>
     */
    public function after(): array
    {
        return $this->saleDetailValidations();
    }
}
