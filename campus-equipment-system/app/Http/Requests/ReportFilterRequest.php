<?php
// Author: Lai Chee Kit
// Module 4: validation for the report filters (date range, category, status, priority)

namespace App\Http\Requests;

use App\Models\Ticket;
use App\Reports\ReportFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    // The route already uses the "admin" middleware
    public function authorize(): bool
    {
        return true;
    }

    // A wrong filter value sends the Admin back to the report with its default filter and a message
    protected function getRedirectUrl(): string
    {
        return route('reports.show', $this->route('type'));
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', Rule::in(array_keys(ReportFilter::PRESETS))],
            'from' => ['nullable', 'required_if:preset,custom', 'date_format:Y-m-d'],
            'to' => ['nullable', 'required_if:preset,custom', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(Ticket::STATES))],
            'priority' => ['nullable', Rule::in(Ticket::PRIORITIES)],
        ];
    }

    public function messages(): array
    {
        return [
            'from.required_if' => 'Please choose a start date for the custom range.',
            'to.required_if' => 'Please choose an end date for the custom range.',
            'to.after_or_equal' => 'The end date must be the same as or after the start date.',
        ];
    }

    public function toFilter(): ReportFilter
    {
        return ReportFilter::fromInput($this->validated());
    }
}
