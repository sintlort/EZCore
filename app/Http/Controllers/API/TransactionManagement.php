<?php

namespace App\Http\Controllers\API;

use App\Helpers\MyDateTime;
use App\Http\Controllers\Controller;
use App\Models\mDetailPembelian;
use App\Models\mMetodePembayaran;
use App\Models\mPembelian;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $pembelian = mPembelian::where('id', $request->id_detail)->first();
        $pembelian2 = mPembelian::with('PDetailPembelian')->where('id', $request->id_detail)->first();
        $pembelian->bukti = $name;
        $pembelian->status = 'terkonfirmasi';

        $pdf = \PDF::loadView('ticket', compact('pembelian2'));
        $output = $pdf->output();
        $filename = time() . Str::random(5);
        $ticketname = $filename. '.pdf';
        Storage::disk('public')->put('/ticket_pdf/' . $ticketname, $output);
        $pembelian->file_tiket = $filename;
        $pembelian->save();

        return response()->json(['message' => 'success', 'transaction' => 'uploaded'], 200);
    }

    public function checkPDF($name)
    {
        return view('CHECK', compact('name'));
    }

    public function transactionCanceled(Request $request)
    {
        $user = Auth::user()->id;
        $data = mPembelian::find($request->id);
        $findTicket = mDetailPembelian::where('status', 'Used')->where('id_pembelian', $data->id)->get();
        if (count($findTicket) <= 0) {
            $data->status = 'dibatalkan';
            $data->save();
            return response()->json(['message' => 'success', 'transaction' => 'canceled'], 200);
        } else {
            return response()->json(['message' => 'failed', 'transaction' => 'some ticket has been used'], 200);
        }
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

    public function getTransactionData(Request $request)
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id',$request->id_detail)->where('id_user', $user)->first();
        $day = MyDateTime::DateToDayConverter($transaction->PDetailHarga->DHJadwal->tanggal);
        $transaction->nama_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
        $transaction->nama_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
        $transaction->kode_pelabuhan_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
        $transaction->kode_pelabuhan_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
        $transaction->status_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
        $transaction->status_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
        $transaction->dermaga_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
        $transaction->dermaga_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
        $transaction->estimasi_waktu = $transaction->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
        $transaction->tanggal = $transaction->PDetailHarga->DHJadwal->tanggal;
        $transaction->metode_pembayaran = $transaction->PMetodePembayaran->nama_metode;
        $transaction->nomor_rekening = $transaction->PMetodePembayaran->nomor_rekening;
        $transaction->hari = $day;
        $transaction->harga = $transaction->PDetailHarga->DHHarga->harga;
        $transaction->nama_kapal = $transaction->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
        $time = Carbon::createFromFormat("H:i:s", $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
        $transaction->waktu_berangkat_asal = $time->format('H:i');
        $time->addMinutes($transaction->PDetailHarga->DHJadwal->estimasi_waktu);
        $transaction->waktu_berangkat_tujuan = $time->format('H:i');
        return response()->json($transaction, 200);
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
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id_user', $user)->orderBy("id", "desc")->get();
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
        $ticket_number = $request->ticket_number;

        $userID = Auth::user()->id;
        $pembelianData = mPembelian::where('id_user', $userID)->where('tanggal', $request->tanggal)->pluck('id');
        $data = mDetailPembelian::where('kode_tiket', $ticket_number)->whereIn('id_pembelian', $pembelianData)->where('status', 'Not Used')->first();
        if (!empty($data)) {
            $data->status = "Used";
            $data->save();
            return response()->json(["message" => 'success', 'data' => $data], 200);
        } else {
            return response()->json(["message" => "not found", 'data' => null], 200);
        }
    }

    public function getTicketData(Request $request)
    {
        $ticket_number = $request->ticket_number;
        $data = mDetailPembelian::with('DPPembelian')->where('kode_tiket', $ticket_number)->first();
        if (!empty($data)) {
            $data->tanggal = $data->DPPembelian->tanggal;
            return response()->json(['message' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['message' => 'not found', 'data' => null], 200);
        }
    }
}
