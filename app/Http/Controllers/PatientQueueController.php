<?php

namespace App\Http\Controllers;

use App\Models\ClinicSession;
use App\Models\Patient;
use App\Models\QueueToken;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientQueueController extends Controller
{
    public function index(): Response
    {
        $sessions = ClinicSession::with('doctor')
            ->whereDate('session_date', Carbon::today())
            ->where('status', '!=', 'completed')
            ->get();

        return Inertia::render('Welcome', [
            'sessions' => $sessions,
        ]);
    }

    public function bookToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:clinic_sessions,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'age' => 'nullable|integer|min:0|max:130',
            'gender' => 'nullable|string|in:Male,Female,Other',
        ]);

        $session = ClinicSession::findOrFail($validated['session_id']);

        // Check if session is full
        $currentCount = QueueToken::where('session_id', $session->id)->count();
        if ($currentCount >= $session->max_tokens) {
            return back()->withErrors(['session_id' => 'This clinic session has reached maximum capacity for today.']);
        }

        // Find or create patient
        $patient = Patient::firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'name' => $validated['name'],
                'age' => $validated['age'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ]
        );

        $patient->update([
            'name' => $validated['name'],
            'age' => $validated['age'] ?? $patient->age,
            'gender' => $validated['gender'] ?? $patient->gender,
        ]);

        // Check if patient already has an active token in this session
        $existingToken = QueueToken::where('session_id', $session->id)
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'calling', 'serving', 'on_hold'])
            ->first();

        if ($existingToken) {
            return redirect()->route('patient.tracker', ['token' => $existingToken->id])
                ->with('info', "You already have active Token #{$existingToken->token_number} for this session!");
        }

        $maxToken = QueueToken::where('session_id', $session->id)->max('token_number') ?? 0;
        $nextTokenNumber = $maxToken + 1;

        $token = QueueToken::create([
            'session_id' => $session->id,
            'doctor_id' => $session->doctor_id,
            'patient_id' => $patient->id,
            'token_number' => $nextTokenNumber,
            'type' => 'online',
            'status' => 'waiting',
        ]);

        return redirect()->route('patient.tracker', ['token' => $token->id])
            ->with('success', "Your Token #{$token->token_number} has been booked successfully!");
    }

    public function tracker(QueueToken $token): Response
    {
        $token->load(['session.doctor', 'patient']);

        return Inertia::render('Patient/Tracker', [
            'token' => $token,
            'initialStatus' => $this->calculateTokenStatus($token),
        ]);
    }

    public function liveStatus(QueueToken $token): JsonResponse
    {
        return response()->json($this->calculateTokenStatus($token));
    }

    private function calculateTokenStatus(QueueToken $token): array
    {
        $session = $token->session;

        $currentServing = QueueToken::where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        // Count patients waiting ahead of this token
        $patientsAhead = QueueToken::where('session_id', $session->id)
            ->where('status', 'waiting')
            ->where('token_number', '<', $token->token_number)
            ->count();

        // Estimated minutes ahead (~5 mins per patient)
        $estimatedMinutes = $patientsAhead * 5;

        return [
            'token_number' => $token->token_number,
            'status' => $token->status,
            'type' => $token->type,
            'notes' => $token->notes,
            'doctor_name' => $session->doctor?->name,
            'doctor_specialization' => $session->doctor?->specialization,
            'room_number' => $session->room_number ?? $session->doctor?->room_number ?? 'Room 01',
            'currentServingNumber' => $currentServing?->token_number,
            'patientsAhead' => $patientsAhead,
            'estimatedMinutes' => $estimatedMinutes,
            'isYourTurn' => in_array($token->status, ['calling', 'serving']),
            'timestamp' => now()->toISOString(),
        ];
    }
}
