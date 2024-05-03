<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\StuffStock;
use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}

    public function index()
    {
        $stuffStock = StuffStock::all();
        $stuff = Stuff::all();

     return ApiFormatter::sendResponse(200, true, 'lihat semua barang', [$stuff, 'barang' => $stuffStock]);
      // return response()->json([
        //     'success' => true,
        //     'message' => 'lihat semua stock barang',
        //     'data' => [
        //         'barang' => $stuff,
        //         'stock barang' => $stuffStock
        //     ]
        //     ]);
    }

       

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total_available' => 'required',
                'total_defec' => 'required',
            ]);

            $stock = StuffStock::updateOrCreate([
                        'stuff_id' => $request->input('stuff_id')
                    ],[
                        'total_available' => $request->input('total_available'), 
                        'total defect' => $request->input('total_defect'),
                    ]);

        return ApiFormatter::sendResponse(201, true, 'barang berhasil disimpan!', $stock);
    } catch (\Throwable $th) {
        if ($th->validator->errors()) {
            return ApiFormatter::sendResponse(400, false, 'terdapat kesalahan input silahkan coba lagi', $th->validator->errors());
        } else {
            return ApiFormatter::sendResponse(400, false, 'terdapat kesalahan input silahkan coba lagi', $th->getMessage());
        }
        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'semua kolom wajib diisi',
        //         'data' => $validator->errors()
        //     ], 400);
        // } else {
        //     $stock = StuffStock::updateOrCreate([
        //         'stuff_id' => $request->input('stuff_id')
        //     ],[
        //         'total_available' => $request->input('total_available'), 
        //         'total defect' => $request->input('total_defect'),
        //     ]);

        //     if ($stock) {
        //         return response()->json([
        //             'success' => true,
        //             'message' => 'stock barang berhasil disimpan',
        //             'data' => $stock
        //         ], 201);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'stock barang gagal disimpan',
        //         ], 400);
        //     }
        // }
    }
}

    public function show($id)
    {
        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "lihat barang dengan id $id", $stock);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "data dengan id $id tidak ditemukan");
        }
    }
        // try {
        //     $stock = StuffStock::with('stuff')->find($id);

        //     return response()->json([
        //         'success' => true,
        //         'message' => "lihat stock barang dengan id $id",
        //         'data' => $stock
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "data dengan id $id tidak ditemukan"
        //     ], 404);
        // }
    public function update(Request $request, $id)
    {
        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);
            
            $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

            $stock->update([
                            'total_available' => $total_available,
                            'total_defect' => $total_defect,
                        ]);
        return ApiFormatter::sendResponse(200, true, "berhasil ubah data dengan id $id");
    } catch (\Thorwable $th) {
        return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
        // try {
        //     $stock = StuffStock::with('stuff')->find($id);

            // $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            // $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

        //     if ($stock) {
        //         $stock->update([
        //             'total_available' => $total_available,
        //             'total_defect' => $total_defect,
        //         ]);

        //         return response()->json([
        //             'success' => true,
        //             'message' => "berhasil ubah data stock dengan id $id",
        //             'data' => $stock
        //         ], 200);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => "proses gagal"
        //         ], 404);
        //     }
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => " proses gagal data dengan id $id tidak ditemukan"
        //     ], 404);
        // }
    }
}

    public function destroy($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            $stuff->delete();
        return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id", ['id' => $id]);
    } catch (\Throwable $th) {
        return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
    }
}

public function  deleted()
{
    try {
        $stocks = StuffStock::onlyTrashed()->get();

        return ApiFormater::sendResponse(200, true, "lihat data stock barang yang dihapus", $stocks);
    } catch (\Throwable $th) {
        return ApiFormater::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
    }
}

public function restore($id)
{
    try {
        $stock = StuffStock::onlyTrashed()->findOrFail($id);
        $has_stock = StuffStock::where('stuff_id', $stock->stuff_id)->get();

        if ($has_stock->count() == 1){
            $message = "data stock sudah ada, tidak boleh ada duplicate data stock untuk satu barang silahkan update data stock dengan id stock $stock->stuff_id";
        } else {
            $stock->restore();
            $message = "berhasil mengembalikan data yang telah dihapus";
        }

        return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stock->stuff_id]);
    } catch (\Throwable $th) {
        return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
    }
}

public function restoreAll()
{
    try {
        $stocks = StuffStock::onlyTrashed()->restore();

        return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan semua data yang telah di hapus");
    } catch (\Throwable $th) {
        return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
    }
}

public function permanentDelete($id)
    {
        try {
            $stock = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

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
            $stocks = StuffStock::onlyTrashed()->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
        // try {
        //     $stock = StuffStock::findOrFail($id);

        //     $stock->delete();

        //     return response()->json([
        //         'success' => true,
        //         'message' => "berhasil hapus data dengan id $id",
        //         'data' => [ 'id' => $id,
        //         ]
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "proses gagal data dengan id $id tidak ditemukan"
        //     ], 404);
        // }
    }

