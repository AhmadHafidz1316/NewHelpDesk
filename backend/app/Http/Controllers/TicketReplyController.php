<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketReplies\StoreTicketReplyRequest;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class TicketReplyController extends Controller
{
    public function __construct(protected TicketService $service)
    {
    }

    public function __invoke(StoreTicketReplyRequest $request): JsonResponse
    {
        try {


            $ticket = $this->service->find($request->ticket_id);

            $this->authorize('create', [TicketReply::class, $ticket]);
            $ada = TicketReply::where('ticket_id', $request->ticket_id)->first();

            if (!$ada) {
                $ticekt = Ticket::where('id', $request->ticket_id)->first();
                $ticekt->update([
                    'first_response' => now(),
                    'response_time' => $ticekt->created_at->diffInMinutes(now())
                ]);
            }
            $ticketReply = $this->service->createReply(
                $ticket,
                $request->safe()->only(['content', 'ticket_id']),
                $request->action
            );

            if ($request->hasFile('attachments')) {
                $this->service->storeAttachments($ticketReply, $request->file('attachments'));
            }



            return $this->successResponse('Reply sent successfully', $ticketReply);
        } catch (AuthorizationException) {
            return $this->notAuthorizedResponse();
        }
    }
}
