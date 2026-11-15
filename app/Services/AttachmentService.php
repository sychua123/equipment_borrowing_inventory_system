<?php
// Author: Lai Chee Kit
// Module 4: saves and removes ticket photos.
// Files go to storage/app/private (not inside "public"), under a random file name,
// so a photo can be opened only through AttachmentController, which checks permission.

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /** @param  UploadedFile[]  $files  already validated (JPG/PNG, size, count) */
    public function store(array $files, Ticket $ticket, ?TicketReply $reply = null): void
    {
        foreach ($files as $file) {
            // store() invents a random name; the user's file name is kept only as text
            $path = $file->store("tickets/{$ticket->id}", 'local');

            $ticket->photos()->create([
                'ticket_reply_id' => $reply?->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    public function delete(TicketAttachment $attachment): void
    {
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();
    }
}
