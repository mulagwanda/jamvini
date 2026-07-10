<?php

namespace Plugins\Support\src\Controllers;

use App\Core\ActivityLogger;
use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Plugins\Support\src\Models\Announcement;
use Plugins\Support\src\Models\Ticket;

class AdminSupportController extends Controller
{
    public function index()
    {
        $stats = [
            'open' => Ticket::whereIn('status', ['open', 'client_replied', 'staff_replied'])->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereNotIn('status', ['closed'])->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'announcements' => Announcement::count(),
        ];
        $tickets = Ticket::with('client')->latest('last_reply_at')->latest()->limit(8)->get();
        $announcements = Announcement::latest()->limit(5)->get();

        return view('plugins.Support::admin.index', compact('stats', 'tickets', 'announcements'));
    }

    public function tickets(Request $request)
    {
        $tickets = Ticket::with('client')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->latest('last_reply_at')
            ->latest()
            ->paginate(20);

        return view('plugins.Support::admin.tickets.index', compact('tickets'));
    }

    public function showTicket(Ticket $ticket)
    {
        $ticket->load(['client', 'replies.client', 'replies.admin']);
        return view('plugins.Support::admin.tickets.show', compact('ticket'));
    }

    public function replyTicket(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
            'is_private' => 'nullable|boolean',
            'status' => 'nullable|in:open,staff_replied,client_replied,on_hold,closed',
        ]);

        $ticket->replies()->create([
            'admin_id' => auth('admin')->id(),
            'author_type' => 'admin',
            'message' => $validated['message'],
            'is_private' => $request->boolean('is_private'),
        ]);

        $ticket->update([
            'status' => $validated['status'] ?? ($request->boolean('is_private') ? $ticket->status : 'staff_replied'),
            'last_reply_at' => now(),
            'closed_at' => ($validated['status'] ?? null) === 'closed' ? now() : $ticket->closed_at,
        ]);

        ActivityLogger::log('support.ticket.replied', 'Ticket', $ticket->id, 'Staff replied to ticket ' . $ticket->ticket_number);
        Action::do('support.ticket_replied', $ticket);

        return back()->with('success', 'Reply added.');
    }

    public function updateTicket(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,staff_replied,client_replied,on_hold,closed',
            'priority' => 'required|in:low,normal,high,urgent',
            'department' => 'required|string|max:100',
        ]);

        $validated['closed_at'] = $validated['status'] === 'closed' ? now() : null;
        $ticket->update($validated);

        return back()->with('success', 'Ticket updated.');
    }

    public function create()
    {
        return view('plugins.Support::admin.announcements.form', ['announcement' => new Announcement()]);
    }

    public function store(Request $request)
    {
        $announcement = Announcement::create($this->announcementData($request));
        if ($announcement->is_published) {
            Action::do('support.announcement_published', $announcement);
        }

        return redirect()->route('admin.support.announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        return view('plugins.Support::admin.announcements.form', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->announcementData($request, $announcement));
        return redirect()->route('admin.support.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.support.announcements.index')->with('success', 'Announcement deleted.');
    }

    public function announcements()
    {
        $announcements = Announcement::latest()->paginate(20);
        return view('plugins.Support::admin.announcements.index', compact('announcements'));
    }

    protected function announcementData(Request $request, ?Announcement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'type' => 'required|in:news,maintenance,incident,release',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['title'], $announcement?->id);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($validated['published_at'] ?? now())
            : ($validated['published_at'] ?? null);
        $validated['created_by'] = $announcement?->created_by ?: auth('admin')->id();

        return $validated;
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'announcement';
        $slug = $base;
        $count = 2;

        while (Announcement::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
