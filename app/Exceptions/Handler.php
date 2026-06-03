<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            $loginParams = $request->input('role') === 'referrer' || $request->query('role') === 'referrer'
                ? ['role' => 'referrer']
                : [];

            return redirect()
                ->route('login', $loginParams)
                ->with('error', 'Your session expired (419). Please sign in again.')
                ->withInput($request->only('email'));
        });
    }
}
