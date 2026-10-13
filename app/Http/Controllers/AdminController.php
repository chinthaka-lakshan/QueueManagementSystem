<?php

namespace App\Http\Controllers;

use App\Models\ClinicSession;
use App\Models\QueueToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $doctors = User::where('role', 'doctor')
            ->withCount(['clinicSessions'])
            ->latest()
            ->get();

        $cashiers = User::where('role', 'cashier')
            ->latest()
            ->get();

        $sessions = ClinicSession::with(['doctor', 'tokens'])
            ->withCount([
                'tokens as total_tokens',
                'tokens as waiting_tokens' => fn($q) => $q->where('status', 'waiting'),
                'tokens as serving_tokens' => fn($q) => $q->whereIn('status', ['calling', 'serving']),
                'tokens as completed_tokens' => fn($q) => $q->where('status', 'completed'),
            ])
            ->latest('session_date')
            ->latest('id')
            ->get();

        $todayTokensCount = QueueToken::whereDate('created_at', Carbon::today())->count();
        $todayCompletedCount = QueueToken::whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'doctors' => $doctors,
            'cashiers' => $cashiers,
            'sessions' => $sessions,
            'stats' => [
                'doctors_count' => $doctors->count(),
                'active_sessions_count' => $sessions->where('status', 'active')->count(),
                'today_tokens' => $todayTokensCount,
                'today_completed' => $todayCompletedCount,
            ],
        ]);
    }

    public function storeDoctor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'room_number' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'room_number' => $validated['room_number'] ?? 'Room 01',
            'specialization' => $validated['specialization'] ?? 'General Practitioner',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Doctor {$validated['name']} registered successfully!");
    }

    public function storeCashier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'room_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'cashier',
            'room_number' => $validated['room_number'] ?? 'Counter 01',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Cashier / Receptionist {$validated['name']} registered successfully!");
    }

    public function toggleStaffStatus(User $user): RedirectResponse
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with('info', "Status updated for {$user->name}.");
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'session_date' => 'required|date',
            'title' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'max_tokens' => 'required|integer|min:1|max:500',
            'status' => 'required|in:active,scheduled,paused,completed',
        ]);

        $doctor = User::findOrFail($validated['doctor_id']);

        $session = ClinicSession::create([
            'doctor_id' => $doctor->id,
            'session_date' => $validated['session_date'],
            'title' => $validated['title'],
            'room_number' => $validated['room_number'] ?: ($doctor->room_number ?? 'Room 01'),
            'max_tokens' => $validated['max_tokens'],
            'status' => $validated['status'],
            'started_at' => $validated['status'] === 'active' ? Carbon::now() : null,
        ]);

        return back()->with('success', "Clinic session '{$session->title}' created successfully for {$doctor->name}!");
    }

    public function updateSessionStatus(Request $request, ClinicSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,scheduled,paused,completed',
        ]);

        $updates = ['status' => $validated['status']];
        if ($validated['status'] === 'active' && !$session->started_at) {
            $updates['started_at'] = Carbon::now();
        } elseif ($validated['status'] === 'completed') {
            $updates['ended_at'] = Carbon::now();
        }

        $session->update($updates);

        return back()->with('success', "Session status updated to {$validated['status']}.");
    }
}
