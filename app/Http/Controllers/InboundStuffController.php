<?php 

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\StuffStock;
use App\Models\InboundStuff;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use IntlChar;

class InboundStuffController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}

    public function index()
    {
        $inboundStock = InboundStuff::with('stuff', 'stuff.stock')->get();


        return ApiFormatter::sendResponse(200, true, 'Lihat semua stok barang', $inboundStock);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id'   => 'required',
            'total' => 'required',
            'date' => 'required',
            'proof_file' => 'required|image',
        ]);


        // if ($validator->fails()) {
        //     return ApiFormatter::sendResponse(400, false, 'Semua Kolom Wajib Diisi!', $validator->errors());
        // } else {
        //     // mengambil file
        //     $file = $request->file('proof_file');
        //     $fileName = $request->input('stuff_id') . '_' . strtotime($request->input('date')) . strtotime(date('H:i')) . '.' . $file->getClientOriginalExtension();
        //     $file->move('proof', $fileName);
        $nameImage = Str::random(5) . "_" . $request->file('proff_file')->getClientOriginalName();
        $request->file('proff_file')->move('upload-images', $nameImage);
        $pathImage = url('upload-images/' . $nameImage);
            $inboundData = InboundStuff::create([
                'stuff_id'     => $request->input('stuff_id'),
                'total'   => $request->input('total'),
                'date'   => $request->input('date'),
                'proof_file'   => $pathImage,
            ]);


            $stock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();


            $total_stock = (int)$stock->total_available + (int)$request->input('total');


            $stock->update([
                'total_available' => (int)$total_stock
            ]);


            if ($inbound && $stock) {
                return ApiFormatter::sendResponse(201, true, 'Barang Masuk Berhasil Disimpan!');
            } else {
                return ApiFormatter::sendResponse(400, false, 'Barang Masuk Gagal Disimpan!');
            }
        }
    public function show($id)
    {
        try {
            $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);


            return ApiFormatter::sendResponse(200, true, "Lihat Barang Masuk dengan id $id", $inbound);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan", $th->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);


            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $inbound->stuff_id;
            $total = ($request->total) ? $request->total : $inbound->total;
            $date = ($request->date) ? $request->date : $inbound->date;


            if ($request->file('proof_file') !== NULL) {
                $file = $request->file('proof_file');
                $fileName = $stuff_id . '_' . strtotime($date) . strtotime(date('H:i')) . '.' . $file->getClientOriginalExtension();
                $file->move('proof', $fileName);
            } else {
                $fileName = $inbound->proof_file;
            }
            $total_s = $total - $inbound->total;
            $total_stock = (int)$inbound->stuff->stock->total_available + $total_s;
            $inbound->stuff->stock->update([
                'total_available' => (int)$total_stock
            ]);
            if ($inbound) {
                $inbound->update([
                    'stuff_id' => $stuff_id,
                    'total' => $total,
                    'date' => $date,
                    'proof_file' => $fileName
                ]);
                return ApiFormatter::sendResponse(200, true, "Berhasil Ubah Data Barang Masuk dengan id $id", $inbound);
            } else {
                return ApiFormatter::sendResponse(400, false, "Proses gagal!");
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(400, false, "Proses Gagal!", $th->getMessage());
        }
    }


    public function destroy($id)
    {
        // pada fitur hapus inbound stuff, tambahlah logic pengkondisian agar data inbound stuff 
        // tidak dapat dihapus apabila total_available pada stuff_stocks lebih kecil dari total pada inbounds
        try {
            $inbound = InboundStuff::findOrFail($id);

            $data = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

            if ($data->total_available < $inbound->total) {
                $inbound->delete();

                return ApiFormatter::sendResponse(404, false, 'Proses gagal total_available pada stuff_stocks lebih kecil dari total pada inbounds');
            } else {
                return ApiFormatter::sendResponse(200, true, 'Berhasil hapus data dengan id $id', [ 'id' => $id,]);
            }

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "Proses gagal silahkan coba lagi", $th->getMessage()); 
        }
    }

    
    public function deleted()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Lihat Data Barang Masuk yang dihapus", $inbounds);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi!", $th->getMessage());
        }
    }

    public function restore( $id)
    {
        try {
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                $restoredData = InboundStuff::find($id);
                $totalRestored = $restoredData->total;
                $stuffId = $restoredData->stuff_id;
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    $stuffStock->total_available += $totalRestored;
                    $stuffStock->save();
                }
    
                return Apiformatter::sendResponse(200, 'success', $restoredData);
            } else {
                return Apiformatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return Apiformatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    public function deletePermanent($id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
            return Apiformatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            return Apiformatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed();

            foreach ($inbounds->get() as $inbound) {
                $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

                $available = $stock->total_available - $inbound->total;
                $defect = ($available < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defect;

                $stock->update([
                    'total_available' => $available,
                    'total_defect' => $defect
                ]);

                $inbound->forceDelete();
            }

            $inbounds->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen semua data yang telah di hapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi!", $th->getMessage());
        }
    }
}
