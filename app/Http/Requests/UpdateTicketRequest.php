<?php
// Author: Lai Chee Kit
// Module 4: validation for editing a ticket (allowed only while it is Open)

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateTicketRequest extends TicketFormRequest
{
    // TicketPolicy@update: the submitter, and the state must allow editing
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        $photoIds = $this->route('ticket')->photos()->pluck('id')->all();

        return parent::rules() + [
            // Photos the user ticked to remove; they must belong to this ticket
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer', Rule::in($photoIds)],
        ];
    }

    // The equipment already on the ticket stays allowed even if it was retired later
    protected function allowedEquipmentIds(): array
    {
        return array_merge(parent::allowedEquipmentIds(), array_filter([$this->route('ticket')->equipment_id]));
    }

    protected function keptPhotoCount(): int
    {
        $ticket = $this->route('ticket');
        $removed = $ticket->photos()->whereIn('id', (array) $this->input('remove_photos', []))->count();

        return $ticket->photos()->count() - $removed;
    }
}
