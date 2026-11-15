<?php
// Author: Lai Chee Kit
// Module 4: validation for the filter boxes above the ticket list.
// SQL Injection defence, part 1: only known values are accepted, and the sort
// option is a fixed word that is later mapped to a column name (never used directly).

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketFilterRequest extends FormRequest
{
    // sort word => [column, direction]. A value that is not a key here is rejected.
    public const SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'updated' => ['updated_at', 'desc'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    // A wrong filter value in the URL sends the user back to the plain list with a message
    protected function getRedirectUrl(): string
    {
        return route('tickets.index');
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(array_keys(Ticket::STATES))],
            'category' => ['nullable', Rule::in(Ticket::CATEGORIES)],
            'priority' => ['nullable', Rule::in(Ticket::PRIORITIES)],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(array_keys(self::SORTS))],
        ];
    }
}
