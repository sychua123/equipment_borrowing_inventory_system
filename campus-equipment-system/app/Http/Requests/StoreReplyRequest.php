<?php
// Author: Lai Chee Kit
// Module 4: validation for adding a reply to a ticket

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    // TicketPolicy@reply: the submitter or the Admin, and the ticket is not Closed
    public function authorize(): bool
    {
        return $this->user()->can('reply', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:'.config('module4.max_photos')],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png', 'max:'.config('module4.max_photo_kb')],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write your reply.',
            'photos.max' => 'A reply can have at most '.config('module4.max_photos').' photos.',
            'photos.*.mimes' => 'Photos must be JPG or PNG files.',
            'photos.*.max' => 'Each photo must be 2 MB or smaller.',
        ];
    }
}
