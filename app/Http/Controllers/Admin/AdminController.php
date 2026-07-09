<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Review;
use App\Models\WebhookEvent;
use App\Services\Ai\AiProviderFactory;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'reviews' => Review::count(),
            'completed_reviews' => Review::where('status', 'completed')->count(),
            'failed_reviews' => Review::where('status', 'failed')->count(),
            'webhook_events' => WebhookEvent::count(),
            'pending_webhooks' => WebhookEvent::where('status', 'received')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // User Management
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update($validated);
        return redirect()->back()->with('success', 'User updated');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete yourself']);
        }
        $user->delete();
        return redirect()->back()->with('success', 'User deleted');
    }

    // AI Provider Management
    public function aiProviders(AiProviderFactory $factory)
    {
        $providers = config('ai.providers', []);
        $default = config('ai.default', 'groq');

        return view('admin.ai-providers', compact('providers', 'default'));
    }

    // Prompt Templates
    public function promptTemplates()
    {
        $templates = config('ai.prompt_templates', []);
        return view('admin.prompts', compact('templates'));
    }

    // Queue / Jobs
    public function queue()
    {
        $failedJobs = \DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(50)->get();
        return view('admin.queue', compact('failedJobs'));
    }

    // Webhook Events
    public function webhooks()
    {
        $events = WebhookEvent::with(['repository', 'pullRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.webhooks', compact('events'));
    }

    // System Settings
    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        // Store system settings in env or config
        $validated = $request->validate([
            'ai_provider' => ['nullable', 'string'],
            'max_review_timeout' => ['nullable', 'integer'],
        ]);

        return redirect()->back()->with('success', 'Settings updated');
    }
}
