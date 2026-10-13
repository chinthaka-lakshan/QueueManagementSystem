<?php

namespace App\Http\Controllers;

use App\Models\ClinicSession;
use App\Models\Patient;
use App\Models\QueueToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashierController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $cashier = $request->user();

        // Get all active sessions for today
        $sessions = ClinicSession::with('doctor')
            ->whereDate('session_date', Carbon::today())
            ->where('status', '!=', 'completed')
            ->get();

        $selectedSessionId = $request->input('session_id', $sessions->first()?->id);
        $currentSession = $sessions->firstWhere('id', $selectedSessionId) ?? $sessions->first();

        $tokens = [];
        if ($currentSession) {
            $tokens = QueueToken::with('patient', 'doctor')
                ->where('session_id', $currentSession->id)
                ->orderBy('token_number')
                ->get();
        }

        $doctors = User::where('role', 'doctor')->where('is_active', true)->get();

        return Inertia::render('Cashier/Dashboard', [
            'cashier' => $cashier,
            'sessions' => $sessions,
            'currentSession' => $currentSession,
            'tokens' => $tokens,
            'doctors' => $doctors,
            'recentlyIssuedToken' => session('issuedToken'),
        ]);
    }

    public function issueToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:clinic_sessions,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'nic' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0|max:130',
            'gender' => 'nullable|string|in:Male,Female,Other',
        ]);

        $session = ClinicSession::findOrFail($validated['session_id']);

        // Find or create patient
        $patient = Patient::firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'name' => $validated['name'],
                'nic' => $validated['nic'] ?? null,
                'age' => $validated['age'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ]
        );

        // Update details if provided
        $patient->update([
            'name' => $validated['name'],
            'nic' => $validated['nic'] ?? $patient->nic,
            'age' => $validated['age'] ?? $patient->age,
            'gender' => $validated['gender'] ?? $patient->gender,
        ]);

        // Next token number in session
        $maxToken = QueueToken::where('session_id', $session->id)->max('token_number') ?? 0;
        $nextTokenNumber = $maxToken + 1;

        $token = QueueToken::create([
            'session_id' => $session->id,
            'doctor_id' => $session->doctor_id,
            'patient_id' => $patient->id,
            'token_number' => $nextTokenNumber,
            'type' => 'walk_in',
            'status' => 'waiting',
        ]);

        $token->load('patient', 'doctor', 'session');

        return back()->with([
            'success' => "Token #{$nextTokenNumber} generated successfully for {$patient->name}!",
            'issuedToken' => $token,
        ]);
    }

    public function searchPatient(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where('phone', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($patients);
    }
}
