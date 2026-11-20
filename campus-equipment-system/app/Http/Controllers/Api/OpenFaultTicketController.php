<?php
// Author: Lai Chee Kit
// Module 4: the web service Module 4 EXPOSES (Module 1 consumes it).
//
//   GET /api/m4/equipment/{equipmentID}/open-fault-tickets?requestID=...&timeStamp=...
//   Header: X-API-KEY
//
// It answers: "How many open fault tickets does this equipment have?"
// Module 1 can show a warning on the equipment page before someone borrows a faulty item.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\Ifa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OpenFaultTicketController extends Controller
{
    public function __invoke(Request $request, string $equipmentId): JsonResponse
    {
        $requestId = $request->query('requestID');

        try {
            // IFA: every request must carry requestID and timeStamp
            $validator = Validator::make(
                ['requestID' => $requestId, 'timeStamp' => $request->query('timeStamp'), 'equipmentID' => $equipmentId],
                [
                    'requestID' => ['required', 'string', 'max:64'],
                    'timeStamp' => ['required', 'date_format:'.Ifa::TIME_FORMAT],
                    'equipmentID' => ['required', 'integer', 'min:1'],
                ],
                [],
                // Keep the field names as they are in error messages
                ['requestID' => 'requestID', 'timeStamp' => 'timeStamp', 'equipmentID' => 'equipmentID'],
            );

            if ($validator->fails()) {
                return Ifa::fail($requestId, 'The request is not valid.', 422, $validator->errors()->toArray());
            }

            // The ID is a bound parameter here as well
            $openFaults = Ticket::query()->openFaults()->where('equipment_id', (int) $equipmentId);

            return Ifa::success($requestId, [
                'equipmentID' => (int) $equipmentId,
                'openFaultCount' => (clone $openFaults)->count(),
                // Only what Module 1 needs: no description, no user details
                'tickets' => $openFaults->latest()->limit(5)->get()
                    ->map(fn (Ticket $ticket) => [
                        'ticketID' => $ticket->id,
                        // The API field name stays "title" (Module 1 reads it); the column is "subject"
                        'title' => $ticket->subject,
                        'priority' => $ticket->priority,
                        'status' => $ticket->status,
                        'submittedAt' => $ticket->created_at->format(Ifa::TIME_FORMAT),
                    ])
                    ->all(),
            ]);
        } catch (Throwable $e) {
            // Write the real error to the log; the caller only gets a general message
            report($e);

            return Ifa::error($requestId);
        }
    }
}
