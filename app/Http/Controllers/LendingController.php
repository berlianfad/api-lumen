<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Lending;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $data = Lending::with('stuff', 'user', 'restoration')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);
            //user_id tidak ke validasi karena value bukan bersumber dr luar (dipilih user)

            //cek total_available stuff terkait
            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Belum ada data inbound!');
            } elseif ((int)$request->total_stuff > (int)$totalAvailable) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Stock tidak tersedia!');
            } else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update([ 'total_available' => $totalAvailableNow ]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stock')->first();

                return ApiFormatter::sendResponse(200, 'success', $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

public function destroy($id)
{
    try {
        $lending = Lending::findOrFail($id);

        if ($lending->restoration()->exists()) {
            return ApiFormatter::sendResponse(400, 'bad request', 'Peminjaman tidak dapat dibatalkan karena sudah ada proses pengembalian');
        }

        $total_stuff = $lending->total_stuff;

        $stuff_stock = StuffStock::where('stuff_id', $lending->stuff_id)->first();
        $stuff_stock->total_available += $total_stuff;
        $stuff_stock->save();

        $lending->delete();

        return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus data peminjaman');
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}
    public function show($id)
    {
        try {
            $data = Lending::where('id', $id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
  }
