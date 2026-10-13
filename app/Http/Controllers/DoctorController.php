<?php

namespace App\Http\Controllers;

use App\Models\ClinicSession;
use App\Models\QueueToken;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $doctor = $request->user();

        // Get or create today's active session
        $session = ClinicSession::where('doctor_id', $doctor->id)
            ->whereDate('session_date', Carbon::today())
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();

        if (!$session) {
            $session = ClinicSession::create([
                'doctor_id' => $doctor->id,
                'session_date' => Carbon::today(),
                'title' => 'Daily Consultation',
                'room_number' => $doctor->room_number ?? 'Room 01',
                'status' => 'active',
                'max_tokens' => 50,
                'started_at' => Carbon::now(),
            ]);
        }

        $tokens = QueueToken::with('patient')
            ->where('session_id', $session->id)
            ->orderBy('token_number')
            ->get();

        $currentServing = $tokens->first(fn($t) => in_array($t->status, ['calling', 'serving']));
        $waitingTokens = $tokens->where('status', 'waiting')->values();
        $onHoldTokens = $tokens->where('status', 'on_hold')->values();
        $completedTokens = $tokens->where('status', 'completed')->values();

        return Inertia::render('Doctor/Dashboard', [
            'doctor' => $doctor,
            'session' => $session,
            'currentServing' => $currentServing,
            'waitingTokens' => $waitingTokens,
            'onHoldTokens' => $onHoldTokens,
            'completedTokens' => $completedTokens,
            'stats' => [
                'total' => $tokens->count(),
                'waiting' => $waitingTokens->count(),
                'on_hold' => $onHoldTokens->count(),
                'completed' => $completedTokens->count(),
            ],
        ]);
    }

    public function callNext(Request $request): RedirectResponse
    {
        $doctor = $request->user();
        $session = ClinicSession::where('doctor_id', $doctor->id)
            ->whereDate('session_date', Carbon::today())
            ->where('status', 'active')
            ->latest()
            ->firstOrFail();

        // If there's an existing serving token, complete it first
        $currentlyServing = QueueToken::where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        if ($currentlyServing) {
            $currentlyServing->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);
        }

        // Find next waiting token
        $nextToken = QueueToken::where('session_id', $session->id)
            ->where('status', 'waiting')
            ->orderBy('token_number')
            ->first();

        if ($nextToken) {
            $nextToken->update([
                'status' => 'calling',
                'called_at' => Carbon::now(),
                'served_at' => Carbon::now(),
            ]);

            return back()->with('success', "Called Token #{$nextToken->token_number}");
        }

        return back()->with('info', 'No waiting patients in queue.');
    }

    public function callSpecificToken(QueueToken $token): RedirectResponse
    {
        // Mark any current serving token as completed
        QueueToken::where('session_id', $token->session_id)
            ->whereIn('status', ['calling', 'serving'])
            ->where('id', '!=', $token->id)
            ->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

        $token->update([
            'status' => 'calling',
            'called_at' => Carbon::now(),
            'served_at' => Carbon::now(),
        ]);

        return back()->with('success', "Now calling Token #{$token->token_number}");
    }

    public function holdToken(Request $request, QueueToken $token): RedirectResponse
    {
        $token->update([
            'status' => 'on_hold',
            'notes' => $request->input('notes', 'Patient skipped / not present when called'),
        ]);

        return back()->with('info', "Token #{$token->token_number} put on hold (Skipped).");
    }

    public function recallToken(QueueToken $token): RedirectResponse
    {
        $token->update([
            'status' => 'calling',
            'called_at' => Carbon::now(),
            'served_at' => Carbon::now(),
            'notes' => 'Recalled from On Hold',
        ]);

        return back()->with('success', "Recalled Token #{$token->token_number} into consultation.");
    }

    public function completeToken(QueueToken $token): RedirectResponse
    {
        $token->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        return back()->with('success', "Token #{$token->token_number} marked as completed.");
    }

    public function cancelToken(QueueToken $token): RedirectResponse
    {
        $token->update([
            'status' => 'cancelled',
        ]);

        return back()->with('info', "Token #{$token->token_number} cancelled.");
    }
}
