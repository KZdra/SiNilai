<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $className = null;
        if (!empty($user->class_id)) {
            $className = DB::table('class')->where('id', $user->class_id)->value('class_name');
        }
        $roleName = DB::table('roles')->where('id', $user->role_id)->value('role_name');

        return view('auth.profile', compact('user', 'className', 'roleName'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();

        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'nip' => !empty($request->nip) ? $request->nip : null,
            'email' => !empty($request->email) ? $request->email : null,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $user->id)->update($updateData);

        return redirect()->route('profile.show')->with('success', 'Profil dan kredensial akun Anda berhasil diperbarui!');
    }
}
