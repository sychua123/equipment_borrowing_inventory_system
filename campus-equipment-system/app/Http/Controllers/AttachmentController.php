<?php
// Author: Lai Chee Kit
// Module 4: shows one ticket photo. Photos are not in the public folder,
// so this is the only way to open one, and it checks permission first.

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function show(TicketAttachment $attachment): StreamedResponse
    {
        // Whoever may view the ticket may view its photos
        Gate::authorize('view', $attachment->ticket);

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->response($attachment->file_path, null, [
            'Content-Type' => $attachment->mime_type,
            // Tell the browser not to guess a different file type
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
