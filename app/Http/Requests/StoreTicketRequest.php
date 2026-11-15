<?php
// Author: Lai Chee Kit
// Module 4: validation for submitting a new ticket

namespace App\Http\Requests;

class StoreTicketRequest extends TicketFormRequest
{
    // Every logged-in user may submit a ticket
    public function authorize(): bool
    {
        return true;
    }

    protected function keptPhotoCount(): int
    {
        return 0;
    }
}
