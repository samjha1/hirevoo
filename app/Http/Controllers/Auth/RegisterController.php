<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $role = $request->query('role', 'candidate');
        if (!in_array($role, ['candidate', 'referrer', 'edtech'], true)) {
            $role = 'candidate';
        }
        return view('hirevo.sign-up', ['defaultRole' => $role]);
    }

    public function register(Request $request)
    {
        $role = $request->input('role', 'candidate');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:candidate,referrer,edtech'],
        ];

        if ($role === 'referrer') {
            $rules['email'][] = new WorkEmail;
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['contact'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        return redirect(route('home'));
    }
}
