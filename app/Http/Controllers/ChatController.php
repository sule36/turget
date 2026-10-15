<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\AntiOffPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        protected AntiOffPlatformService $antiLeakService
    ) {}

    /**
     * List user conversations (PRD §13).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne:id,name', 'userTwo:id,name', 'tripInquiry:id,title', 'messages' => fn ($q) => $q->latest('id')->limit(1)])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show single chat room with anti-off-platform banner and real-time/polling messages.
     */
    public function show(Request $request, Conversation $conversation): Response
    {
        $user = $request->user();

        if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
            abort(403);
        }

        $otherUser = $conversation->otherUser($user);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->oldest('id')
            ->get();

        // Mark unread messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Chat/Room', [
            'conversation' => $conversation->load(['tripInquiry:id,title']),
            'otherUser' => $otherUser,
            'messages' => $messages,
        ]);
    }

    /**
     * Send message through Anti-Off-Platform Inspection Engine (PRD §13.2).
     */
    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $inspection = $this->antiLeakService->inspect($validated['message']);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'original_body' => $validated['message'],
            'clean_body' => $inspection['clean_body'],
            'is_flagged' => $inspection['is_flagged'],
            'flagged_reasons' => $inspection['flagged_reasons'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $flashMessage = $inspection['is_flagged']
            ? 'Pesan dikirim. Nomor kontak/link luar disamarkan secara otomatis demi keamanan transaksi Anda.'
            : null;

        return back()->with($flashMessage ? ['warning' => $flashMessage] : []);
    }

    /**
     * Start or resume conversation with guide/vendor.
     */
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $targetUserId = (int) $request->input('target_user_id');
        $tripInquiryId = $request->input('trip_inquiry_id');

        if ($user->id === $targetUserId) {
            return back()->with('error', 'Tidak dapat mengirim pesan ke akun sendiri.');
        }

        $targetUser = User::findOrFail($targetUserId);

        $conversation = Conversation::where(function ($q) use ($user, $targetUser) {
            $q->where('user_one_id', $user->id)->where('user_two_id', $targetUser->id);
        })->orWhere(function ($q) use ($user, $targetUser) {
            $q->where('user_one_id', $targetUser->id)->where('user_two_id', $user->id);
        })->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $user->id,
                'user_two_id' => $targetUser->id,
                'trip_inquiry_id' => $tripInquiryId,
                'last_message_at' => now(),
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }
}
