<?php
// Author: Lai Chee Kit
// Module 4: validation rules shared by "submit ticket" and "edit ticket"

namespace App\Http\Requests;

use App\Models\Ticket;
use App\Services\EquipmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class TicketFormRequest extends FormRequest
{
    // How many photos the ticket keeps, before the newly uploaded ones are added
    abstract protected function keptPhotoCount(): int;

    // Equipment IDs the user may choose (Retired equipment is not offered)
    protected function allowedEquipmentIds(): array
    {
        return array_column(app(EquipmentService::class)->selectable(), 'id');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(Ticket::CATEGORIES)],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            // A Fault ticket must be linked to an equipment item
            'equipment_id' => ['nullable', 'required_if:category,Fault', 'integer', Rule::in($this->allowedEquipmentIds())],
            // Photos: JPG/PNG only, at most 2 MB each
            'photos' => ['nullable', 'array', 'max:'.config('module4.max_photos')],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png', 'max:'.config('module4.max_photo_kb')],
        ];
    }

    // Extra check after the normal rules: at most 3 photos per ticket in total
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $total = $this->keptPhotoCount() + count($this->file('photos', []));

                if ($total > config('module4.max_photos')) {
                    $validator->errors()->add('photos', 'A ticket can have at most '.config('module4.max_photos').' photos.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_id.required_if' => 'Please choose the equipment that has the fault.',
            'equipment_id.in' => 'Please choose an equipment item from the list.',
            'photos.*.mimes' => 'Photos must be JPG or PNG files.',
            'photos.*.max' => 'Each photo must be 2 MB or smaller.',
        ];
    }

    // Only these fields are saved into the tickets table
    public function ticketData(): array
    {
        $data = $this->safe()->only(['title', 'description', 'category', 'priority', 'equipment_id']);

        // The form field is "title"; the template's column is "subject"
        $data['subject'] = $data['title'];
        unset($data['title']);

        return $data;
    }
}
