<?php

namespace App\Http\Controllers;

use App\Models\ClinicSession;
use App\Models\QueueToken;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function index(?int $sessionId = null): Response
    {
        $sessions = ClinicSession::with('doctor')
            ->whereDate('session_date', Carbon::today())
            ->where('status', '!=', 'completed')
            ->get();

        $activeSession = $sessionId
            ? $sessions->firstWhere('id', $sessionId)
            : $sessions->first();

        return Inertia::render('Display/WaitingScreen', [
            'sessions' => $sessions,
            'initialSessionId' => $activeSession?->id,
            'initialData' => $activeSession ? $this->getDisplayPayload($activeSession->id) : null,
        ]);
    }

    public function liveData(Request $request, ?int $sessionId = null): JsonResponse
    {
        if (!$sessionId) {
            $firstActive = ClinicSession::whereDate('session_date', Carbon::today())
                ->where('status', 'active')
                ->first();
            $sessionId = $firstActive?->id;
        }

        if (!$sessionId) {
            return response()->json([
                'session' => null,
                'currentServing' => null,
                'lastCalled' => [],
                'waitingList' => [],
                'onHoldList' => [],
                'timestamp' => now()->toISOString(),
            ]);
        }

        return response()->json($this->getDisplayPayload($sessionId));
    }

    private function getDisplayPayload(int $sessionId): array
    {
        $session = ClinicSession::with('doctor')->find($sessionId);
        if (!$session) {
            return [];
        }

        $allTokens = QueueToken::with('patient')
            ->where('session_id', $sessionId)
            ->orderBy('token_number')
            ->get();

        $currentServing = $allTokens->first(fn($t) => in_array($t->status, ['calling', 'serving']));

        $completedOrServed = $allTokens
            ->filter(fn($t) => $t->called_at !== null && $t->id !== $currentServing?->id)
            ->sortByDesc('called_at')
            ->take(5)
            ->values();

        $waitingList = $allTokens
            ->filter(fn($t) => $t->status === 'waiting')
            ->take(8)
            ->values();

        $onHoldList = $allTokens
            ->filter(fn($t) => $t->status === 'on_hold')
            ->values();

        return [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'doctor_name' => $session->doctor?->name,
                'doctor_specialization' => $session->doctor?->specialization,
                'room_number' => $session->room_number ?? $session->doctor?->room_number ?? 'Room 01',
                'status' => $session->status,
            ],
            'currentServing' => $currentServing ? [
                'id' => $currentServing->id,
                'token_number' => $currentServing->token_number,
                'patient_name' => $currentServing->patient?->name,
                'type' => $currentServing->type,
                'status' => $currentServing->status,
                'called_at' => $currentServing->called_at?->toIso8601String(),
            ] : null,
            'lastCalled' => $completedOrServed->map(fn($t) => [
                'token_number' => $t->token_number,
                'status' => $t->status,
                'called_at' => $t->called_at?->format('h:i A'),
            ]),
            'waitingList' => $waitingList->map(fn($t) => [
                'token_number' => $t->token_number,
                'type' => $t->type,
            ]),
            'onHoldList' => $onHoldList->map(fn($t) => [
                'token_number' => $t->token_number,
                'patient_name' => $t->patient?->name,
            ]),
            'stats' => [
                'total' => $allTokens->count(),
                'waiting' => $waitingList->count(),
                'completed' => $allTokens->where('status', 'completed')->count(),
                'on_hold' => $onHoldList->count(),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
