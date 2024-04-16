<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class UserController extends Controller
{
    public function index()
    {
        $user = User::get();

        return ApiFormatter::sendResponse(200, true, 'lihat semua user', $user);
    }

    public function store(Request $request)
    {
    try {
        $this->validate($request, [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role' => 'required|in:staff,admin',
        ]);

        $user = User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
        ]);

        return ApiFormatter::sendResponse(201, true, 'user berhasil disimpan!', $user);
    } catch (\Throwable $th) {
        if ($th->validator->error()) {
            return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input Silahkan coba lagi', $th->validator->error());
        } else {
            return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input Silahkan coba lagi', $th->getMessage());
        }
    }
}
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 
            "Lihat User dengan id $id", $user);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 
            "User dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
        $user = User::findOrFail($id);
        $username = ($request->username) ? $request->username : $user->username;
        $email = ($request->email) ? $request->email : $user->email;
        $password = ($request->password) ? $request->password : $user->password;
        $role = ($request->role) ? $request->role : $user->role;

        $user->update([
            'username'     => $username,
            'email' => $email,
            'password'     => $password,
            'role' => $role,
        ]);

        return ApiFormatter::sendResponse(200, true,
        "Berhasil ubah data dengan id $id");
    } catch (\Throwable $th) {
        return ApiFormatter::sendResponse(404, false,
        "Proses Gagal! silahkan coba lagi!", $th->getMessage());
    }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus data barang dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $users = User::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true,
            "Lihat Data barang yang dihapus", $users);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try{
            $user = User::onlyTrashed()->where('id', $id);

            $user->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagal silahkan coba lagi', $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try{
            $users = User::onlyTrashed();

            $users->restore();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil mengembalikan data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 
            "Proses gagal! silahkan coba lagi", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $user = User::onlyTrashed()->where('id', $id)
            ->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $users = User::onlyTrashed();

            $users->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}