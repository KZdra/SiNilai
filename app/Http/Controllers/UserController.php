<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users as u')->select(
            'u.id',
            'u.class_id',
            'c.class_name',
            'u.role_id',
            'r.role_name',
            'u.username',
            'u.name',
            DB::raw('COALESCE(u.nip, "-") AS nip'),
            'u.email'
        )
            ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
            ->leftJoin('class as c', 'u.class_id', '=', 'c.id')  // Changed alias from 'r' to 'c'
            ->orderBy('u.name', 'asc')->get();
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $rolesList = DB::table('roles')->select('id', 'role_name')->orderBy('role_name', 'asc')->get();

        return view('users.index', compact('users', 'classList', 'rolesList'));
    }

    public function store(Request $request)
    {
        $kont = $request->validate([
            'class_id' => 'required|integer',
            'role_id' => 'required|integer',
            'nip' => 'required|numeric',
            'nama' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);
        try {
            DB::table('users')->insert([
                'class_id' => $kont['class_id'],
                'role_id' => $kont['role_id'],
                'nip' => $kont['nip'],
                'name' => $kont['nama'],
                'username' => $kont['username'],
                'password' => Hash::make($kont['password']),
                'email' => $kont['email'],
                'created_at' => Carbon::now(),
            ]);
            return response()->json(['message' => 'User berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            // return response()->json(['message' => 'Ada Masalah Diantara Input/Server'], 500);
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
    public function update(Request $request, $id)
    {
        try {

            $data = [
                'class_id' => $request->class_id,
                'role_id' => $request->role_id,
                'nip' => $request->nip,
                'name' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'updated_at' => Carbon::now(),
            ];

            if ($request->filled('password')) { // Gunakan filled() untuk mengecek apakah password dikirim
                $data['password'] = Hash::make($request->password);
            }
            DB::table('users')->where('id', $id)->update($data);
            return response()->json(['message' => 'User berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ada Masalah Diantara Input/Server'], 500);
            return response()->json(['message' => $e->getMessages()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::table('users')->where('id', $id)->delete();
            return response()->json(['message' => 'User berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            // return response()->json(['message' => 'Ada Masalah Diantara Input/Server'], 500);
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
