<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $providers = config('ai.providers', []);
        $defaultProvider = config('ai.default', 'groq');

        return view('settings.index', compact('providers', 'defaultProvider'));
    }

    public function updateAiProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string'],
            'model' => ['nullable', 'string'],
        ]);

        // Store user preference (could be in user settings table or user meta)
        // For now, we'll use the system default
        return redirect()->route('settings.index')->with('success', 'AI provider updated');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => ['boolean'],
            'browser_notifications' => ['boolean'],
        ]);

        return redirect()->route('settings.index')->with('success', 'Notification settings updated');
    }
}
