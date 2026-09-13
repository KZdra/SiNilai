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
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Izinkan guru mapel (non-walas) untuk mengakses fitur upload nilai Excel
        if ($request->is('upload-nilai-excel*') || $request->is('*/upload-nilai-excel*')) {
            return $next($request);
        }

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
