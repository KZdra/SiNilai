<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAssignedClass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role_id != 1 && $user->class_id === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda Belum Ditugaskan Menjadi Wali Kelas. Silahkan Hubungi Admin.'], 403);
            }
            abort(403, 'Anda Belum Ditugaskan Menjadi Wali Kelas. Silahkan Hubungi Admin.');
        }

        return $next($request);
    }
}
