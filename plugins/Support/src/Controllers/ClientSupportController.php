<?php

namespace Plugins\Support\src\Controllers;

use App\Core\ActivityLogger;
use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Support\src\Models\Announcement;
use Plugins\Support\src\Models\Ticket;

class ClientSupportController extends Controller
{
    public function announcements()
    {
        $announcements = Announcement::published()->latest('published_at')->paginate(12);
        return view('plugins.Support::public.announcements', compact('announcements'));
    }

    public function announcement(Announcement $announcement)
    {
        abort_unless($announcement->is_published && (!$announcement->published_at || $announcement->published_at->lte(now())), 404);
        return view('plugins.Support::public.announcement', compact('announcement'));
    }

    public function index()
    {
        $client = Auth::guard('web')->user();
        $tickets = Ticket::with('publicReplies')
            ->where('client_id', $client->id)
            ->latest('last_reply_at')
            ->latest()
            ->paginate(12);
        $announcements = Announcement::published()->latest('published_at')->limit(4)->get();

        return view('plugins.Support::client.index', compact('client', 'tickets', 'announcements'));
    }

    public function createTicket()
    {
        $client = Auth::guard('web')->user();
        $services = $client->services()->with('service')->where('status', 'active')->get();

        return view('plugins.Support::client.create-ticket', compact('client', 'services'));
    }

    public function storeTicket(Request $request)
    {
        $client = Auth::guard('web')->user();
        $validated = $request->validate([
            'department' => 'required|string|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'related_service_id' => 'nullable|exists:client_services,id',
        ]);

        if (!empty($validated['related_service_id'])) {
            abort_unless($client->services()->whereKey($validated['related_service_id'])->exists(), 403);
        }

        $ticket = Ticket::create([
            'ticket_number' => $this->ticketNumber(),
            'client_id' => $client->id,
            'department' => $validated['department'],
            'priority' => $validated['priority'],
            'subject' => $validated['subject'],
            'status' => 'open',
            'related_service_id' => $validated['related_service_id'] ?? null,
            'last_reply_at' => now(),
        ]);

        $ticket->replies()->create([
            'client_id' => $client->id,
            'author_type' => 'client',
            'message' => $validated['message'],
        ]);

        ActivityLogger::log('support.ticket.created', 'Ticket', $ticket->id, 'Client opened ticket ' . $ticket->ticket_number);
        Action::do('support.ticket_created', $ticket);

        return redirect()->route('client.support.tickets.show', $ticket)->with('success', 'Ticket opened.');
    }

    public function showTicket(Ticket $ticket)
    {
        $client = Auth::guard('web')->user();
        abort_unless($ticket->client_id === $client->id, 403);
        $ticket->load(['publicReplies.client', 'publicReplies.admin']);

        return view('plugins.Support::client.ticket', compact('client', 'ticket'));
    }

    public function replyTicket(Request $request, Ticket $ticket)
    {
        $client = Auth::guard('web')->user();
        abort_unless($ticket->client_id === $client->id, 403);

        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);

        $ticket->replies()->create([
            'client_id' => $client->id,
            'author_type' => 'client',
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'status' => 'client_replied',
            'last_reply_at' => now(),
            'closed_at' => null,
        ]);

        ActivityLogger::log('support.ticket.client_replied', 'Ticket', $ticket->id, 'Client replied to ticket ' . $ticket->ticket_number);
        Action::do('support.ticket_replied', $ticket);

        return back()->with('success', 'Reply sent.');
    }

    protected function ticketNumber(): string
    {
        $prefix = 'TKT-' . now()->format('Y') . '-';
        $sequence = Ticket::withTrashed()->where('ticket_number', 'like', $prefix . '%')->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT);
        } while (Ticket::withTrashed()->where('ticket_number', $number)->exists());

        return $number;
    }
}
