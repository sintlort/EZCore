<?php

namespace App\Http\Controllers\API;

use App\Helpers\MyDateTime;
use App\Http\Controllers\Controller;
use App\Models\mDetailGolongan;
use App\Models\mDetailJadwal;
use App\Models\mGolongan;
use App\Models\mJadwal;
use App\Models\mKapal;
use App\Models\mPelabuhan;
use Illuminate\Http\Request;

class ScheduleManagement extends Controller
{
    public function indexPelabuhan()
    {
        $pelabuhan = mPelabuhan::all();
        if (!$pelabuhan->isEmpty()) {
            return response()->json(['message' => 'success', 'data' => $pelabuhan], 200);
        }
        return response()->json(['message' => 'bad request'], 400);
    }

    public function indexGolongan(Request $request)
    {
        $golongan = mGolongan::where('id_pelabuhan', $request->asal_pelabuhan)->get();
        return response()->json(['message' => 'success', 'data' => $golongan], 200);
    }

    public function searchSchedule(Request $request)
    {
        if ($request->golongan != "") {
            $detailGolongan = mDetailGolongan::where('id_golongan',$request->golongan)->pluck('id_kapal');
            $kapal = mKapal::whereIn('id',$detailGolongan)->where('tipe_kapal','feri')->pluck('id');
            $jadwal = mJadwal::whereIn('id_kapal',$kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)
                ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
            $day = MyDateTime::DateToDayConverter($request->date);
            $schedule = mDetailJadwal::whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
            return response()->json(['message' => 'success', 'data' => $schedule], 200);
        } else {
            if ($request->tipe_kapal == 'feri') {
                $kapal = mKapal::where('tipe_kapal','feri')->pluck('id');
                $jadwal = mJadwal::whereIn('id_kapal',$kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)
                ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $day = MyDateTime::DateToDayConverter($request->date);
                $schedule = mDetailJadwal::whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
                return response()->json(['message' => 'success', 'data' => $schedule], 200);
            } else {
                $kapal = mKapal::where('tipe_kapal','speedboat')->pluck('id');
                $jadwal = mJadwal::whereIn('id_kapal',$kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)
                    ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $jadwal = mJadwal::where('id_asal_pelabuhan', $request->asal_pelabuhan)
                    ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $day = MyDateTime::DateToDayConverter($request->date);
                $schedule = mDetailJadwal::whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
                return response()->json(['message' => 'success', 'data' => $schedule], 200);
            }
        }
    }
}
