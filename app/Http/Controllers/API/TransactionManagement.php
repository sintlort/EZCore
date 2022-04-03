<?php

namespace App\Http\Controllers\API;

use App\Helpers\MyDateTime;
use App\Http\Controllers\Controller;
use App\Models\mDetailPembelian;
use App\Models\mMetodePembayaran;
use App\Models\mPembelian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionManagement extends Controller
{
    public function metodePembayaran()
    {
        $metode = mMetodePembayaran::all();
        return response()->json($metode, 200);
    }

    public function imageUpload(Request $request)
    {
        $name = $request->file('image')->hashName();

        $path = $request->file('image')->store('public/images');

        $pembelian = mPembelian::find($request->id_detail);
        $pembelian->bukti = $name;
        $pembelian->save();

        return response()->json(['message' => 'success', 'transaction' => 'uploaded'], 200);
    }

    public function transactionCanceled(Request $request)
    {
        $user = Auth::user()->id;
        $data = mPembelian::find($request->id);
        $data->status = 'dibatalkan';
        $data->save();
        return response()->json(['message' => 'success', 'transaction' => 'canceled'], 200);
    }

    public function transactionCommited(Request $request)
    {
        $pembelian = mPembelian::create([
            'id_metode_pembayaran' => $request->id_metode_pembayaran,
            'id_jadwal' => $request->id_detail,
            'id_user' => Auth::user()->id,
            'tanggal' => $request->tanggal,
            'status' => 'menunggu pembayaran',
        ]);

        return response()->json(['message' => 'success', 'data' => $pembelian], 200);
    }

    public function transactionCommitedForPenumpang(Request $request)
    {

        $maxPembelian = mDetailPembelian::max('kode_tiket');
        $detailPembelian = mDetailPembelian::create([
            'id_pembelian' => $request->id_detail_pemesanan,
            'no_id_card' => $request->telepon,
            'kode_tiket' => $maxPembelian + 1,
            'nama_pemegang_tiket' => $request->nama_pemegang_tiket,
            'status' => 'Not Used',
        ]);

        return response()->json(['message' => 'success', 'data' => $detailPembelian], 200);
    }

    public function getTransactionRecently()
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id_user', $user)->where('status', 'menunggu pembayaran')->get();
        foreach ($transaction as $index => $data) {
            $day = MyDateTime::DateToDayConverter($data->PDetailHarga->DHJadwal->tanggal);
            $transaction[$index]->nama_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->nama_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->kode_pelabuhan_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->kode_pelabuhan_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->status_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
            $transaction[$index]->status_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
            $transaction[$index]->dermaga_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
            $transaction[$index]->dermaga_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
            $transaction[$index]->estimasi_waktu = $data->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
            $transaction[$index]->tanggal = $data->PDetailHarga->DHJadwal->tanggal;
            $transaction[$index]->status = $data->status;
            $transaction[$index]->metode_pembayaran = $data->PMetodePembayaran->nama_metode;
            $transaction[$index]->nomor_rekening = $data->PMetodePembayaran->nomor_rekening;
            $transaction[$index]->hari = $day;
            $transaction[$index]->harga = $data->PDetailHarga->DHHarga->harga;
            $transaction[$index]->nama_kapal = $data->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
            $time = Carbon::createFromFormat("H:i:s", $data->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
            $transaction[$index]->waktu_berangkat_asal = $time->format('H:i');
            $time->addMinutes($data->PDetailHarga->DHJadwal->estimasi_waktu);
            $transaction[$index]->waktu_berangkat_tujuan = $time->format('H:i');
        }

        return response()->json($transaction, 200);
    }

    public function getTransactionHistory()
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id_user', $user)->where('status', '!=', 'menunggu pembayaran')->get();
        foreach ($transaction as $index => $data) {
            $day = MyDateTime::DateToDayConverter($data->PDetailHarga->DHJadwal->tanggal);
            $transaction[$index]->nama_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->nama_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->kode_pelabuhan_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->kode_pelabuhan_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->status_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
            $transaction[$index]->status_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
            $transaction[$index]->dermaga_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
            $transaction[$index]->dermaga_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
            $transaction[$index]->estimasi_waktu = $data->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
            $transaction[$index]->tanggal = $data->PDetailHarga->DHJadwal->tanggal;
            $transaction[$index]->metode_pembayaran = $data->PMetodePembayaran->nama_metode;
            $transaction[$index]->nomor_rekening = $data->PMetodePembayaran->nomor_rekening;
            $transaction[$index]->hari = $day;
            $transaction[$index]->harga = $data->PDetailHarga->DHHarga->harga;
            $transaction[$index]->nama_kapal = $data->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
            $time = Carbon::createFromFormat("H:i:s", $data->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
            $transaction[$index]->waktu_berangkat_asal = $time->format('H:i');
            $time->addMinutes($data->PDetailHarga->DHJadwal->estimasi_waktu);
            $transaction[$index]->waktu_berangkat_tujuan = $time->format('H:i');
        }

        return response()->json($transaction, 200);
    }

    public function getPenumpang(Request $request)
    {
        $user = Auth::user()->id;
        $transaction = mDetailPembelian::where('id_pembelian', $request->id)->get();
        return response()->json($transaction, 200);
    }

    public function checkTicket(Request $request)
    {
        $ticket_number = decrypt($request->ticket_number);
        $data = mDetailPembelian::where('kode_tiket',$ticket_number)->where('status','Not Used')->first();
        if(!empty($data)){
            $data->status = "Used";
            $data->save();
            return response()->json(["message"=>'success','data'=>$data],200);
        } else {
            return response()->json(["message"=>"not found",'data'=>null], 200);
        }
    }
}
