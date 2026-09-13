<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicVerificationController extends Controller
{
    /**
     * Verify official report card via QR code token.
     */
    public function verify($token)
    {
        $record = DB::table('catatan_walikelas as cw')
            ->join('students as s', 'cw.student_id', '=', 's.id')
            ->join('class as c', 'cw.class_id', '=', 'c.id')
            ->join('m_fst_pembelajaran as f', 'cw.fst_id', '=', 'f.id')
            ->where('cw.verification_token', $token)
            ->select(
                'cw.*',
                's.nama as student_name',
                's.nis',
                's.nisn',
                's.jenis_kelamin',
                'c.class_name',
                'f.fase',
                'f.semester',
                'f.tahun_ajaran',
                'f.ta'
            )
            ->first();

        $school = \App\Services\MasterDataCache::getSchoolData();

        // Get Wali Kelas name from user assigned to this class
        $waliKelas = DB::table('users')
            ->where('class_id', $record ? $record->class_id : 0)
            ->value('name') ?: 'Wali Kelas';

        return view('public.verify', [
            'valid'     => !is_null($record),
            'record'    => $record,
            'school'    => $school,
            'waliKelas' => $waliKelas,
            'token'     => $token,
        ]);
    }
}
