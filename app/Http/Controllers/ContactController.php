<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Handle the contact form submission.
     */
    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // TODO: Send email or store in DB (e.g. contact_messages table)
        // Mail::to('contact@hirevo.com')->send(new ContactFormMail($request->only(['name', 'email', 'subject', 'message'])));

        return redirect()->route('contact')
            ->with('success', 'Thank you for your message. We will get back to you soon.');
    }
}
