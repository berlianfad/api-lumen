<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use Illuminate\Support\Facades\Validator;

class StuffController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}

    public function index()
    // {
    //     $stuff = Stuff::with('stuffStock')->get();
    //     return ApiFormatter::sendResponse(200, true, 'lihat semua barang', $stuff);
    // }
    {
        try {
            $data = Stuff::with('stock')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);
            $stuff = Stuff::create([
                'name' => $request->input ('name'),
                'category' => $request->input ('category'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'barang berhasil disimpan!', $stuff);
        } catch (\Throwable $th) {
            // if ($th->validator->errors() !== null) {
            //     return ApiFormatter::sendResponse(400, false, 'terdapat kesalahan input silahkan coba lagi', $th->validator->errors());
            // } else {
                return ApiFormatter::sendResponse(400, false, 'terdapat kesalahan input silahkan coba lagi', $th->getMessage());
            // }
        }
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     'category' => 'required',
        // ]);

        // if ($validator->fails()) {
            
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'semua kolom wajib diisi',
        //         'data' => $validator->errors()
        //     ],400);
        // } else {

        //     $stuff = Stuff::create([
        //         'name' => $request->input ('name'),
        //         'category' => $request->input ('category'),
        //     ]);

        //     if ($stuff) {
        //         return response()->json([
        //             'succes' => true,
        //             'message' => 'barang berhasil disimpan',
        //             'data' => $stuff
        //         ],201);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'barang gagal disimpan',
        //         ], 400);
        //     }
        // }
    }
    
    public function show($id)
    {
        try {
            $stuff = Stuff::with('stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "lihat barang dengan id $id", $stuff);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "data dengan id $id tidak ditemukan");
        }
        // try {
        //     $stuff = Stuff::findOrFail($id);
    
        //     return response()->json([
        //         'success' => true,
        //         'message' => "lihat barang dengan id $id",
        //         'data' => $stuff
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "data dengan id $id tidak ditemukan"
        //     ], 404);
        // }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;
            
            $stuff->update([
                'name' => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true, "berhasil ubah data dengan id $id");
        } catch (\Thorwable $th) {
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
            // if  ($stuff) {
            //     $stuff->update([
            //         'name' => $name,
            //         'category' => $category
            //     ]);

            //     return response()->json([
            //         'success' => true,
            //         'message' => "berhasil ubah data dengan id $id",
            //         'data' => $stuff
            //     ], 200);
            // } else {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "proses gagal"
            //     ], 404);
            // }
            // } catch (\Throwable $th) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "proses gagal data dengan id $id tidak ditemukan"
            //     ], 404);
            }
        }

        public function destroy($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            if ($stuff->inboundStuff()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data inbound");
            } 
            elseif ($stuff->stock()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data stuff stock");
            }
            elseif($stuff->lendings()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data lending");
            } 
            elseif ($stuff->inboundStuff()->exists() && $stuff->stocks()->exists() && $stuff->lendings()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data inbound/stuff stock/lending");
            }
            else {
                $stuff->delete();

                return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id", ['id' => $id]);
            }

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

        public function deleted()
        {
            try {
                $stuffs = Stuff::onlyTrashed()->get();

                return ApiFormatter::sendResponse(200, true, "lihat data barang yang diapus", $stuffs);
            } catch (\Thorwable $th) {
                return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
            }
        }

        public function restore($id)
        {
            try {
                $stuff = Stuff::onlyTrashed()->where('id', $id);

                $stuff->restore();
                return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan data yang telah dihapus", ['id' => $id]);
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
            } 
        }

        public function restoreAll()
        {
            try {
                $stuffs = Stuff::onlyTrashed();
                $stuffs->restore();
                return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan semua data yang telah di hapus");
            } catch (\Thorwable $th) {
                return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
            }
        }

        public function permanentDelete($id)
        {
            try {
                $stuff = Stuff::onlyTrashed()->where('id', $id)->forceDelete();
                return ApiFormatter::sendResponse(200, true, "berhasil hapus permanen data yang telah dihapus", ['id'=> $id]);
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
            }
        }
        
        public function permanentDeleteAll()
        {
            try {
                $stuffs= Stuff::onlyTrashed();
                $stuffs-> forceDelete();
                return ApiFormatter::sendResponse(200, true, "berhasil hapus permanen semua data yang telah dihapus");
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
            }
        }
    }
