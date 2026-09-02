<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CbtSyncController extends Controller
{
    /**
     * Return list of all classes with delta sync support.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function classes(Request $request): JsonResponse
    {
        $query = DB::table('class')
            ->select([
                'id',
                'class_name',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $classes = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: Classes retrieved', [
            'count' => $classes->count(),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $classes,
        ]);
    }

    /**
     * Return list of students joined with class table, with optional ?class_id=X and ?since=YYYY-MM-DD filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function students(Request $request): JsonResponse
    {
        $genderField = Schema::hasColumn('students', 'gender') ? 's.gender' : 's.jenis_kelamin as gender';

        $query = DB::table('students as s')
            ->join('class as c', 's.class_id', '=', 'c.id')
            ->select(
                's.nis',
                's.nisn',
                's.nama',
                $genderField,
                's.class_id',
                'c.class_name',
                DB::raw('COALESCE(s.updated_at, s.created_at) as updated_at')
            );

        if ($request->filled('class_id')) {
            $query->where('s.class_id', $request->query('class_id'));
        }

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('s.updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('s.updated_at')
                            ->where('s.created_at', '>=', $since);
                    });
            });
        }

        $students = $query->orderBy('s.class_id', 'asc')
            ->orderBy('s.nama', 'asc')
            ->get();

        Log::channel('cbt_sync')->info('CBT Sync: Students retrieved', [
            'count' => $students->count(),
            'class_id' => $request->query('class_id'),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $students,
        ]);
    }

    /**
     * Return list of all FST (Fase / Semester / Tahun Ajaran) for CBT Academic Years sync.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fst(Request $request): JsonResponse
    {
        $query = DB::table('m_fst_pembelajaran')
            ->select([
                'id',
                'fase',
                'semester',
                'tahun_ajaran',
                'ta',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $fsts = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: FST retrieved', [
            'count' => $fsts->count(),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $fsts,
        ]);
    }

    /**
     * Return list of Mata Pelajaran for CBT Subjects sync with optional class_id, fst_id, and since filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function mapel(Request $request): JsonResponse
    {
        $query = DB::table('mata_pelajarans')
            ->select([
                'id',
                'nama_mapel',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('class_id') || $request->filled('fst_id')) {
            $query->whereIn('id', function ($sub) use ($request) {
                $sub->select('mapel_id')->from('mapel_class_fst');
                if ($request->filled('class_id')) {
                    $sub->where('class_id', $request->query('class_id'));
                }
                if ($request->filled('fst_id')) {
                    $sub->where('fst_id', $request->query('fst_id'));
                }
                $sub->where('is_active', 1);
            });
        }

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $mapels = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: Mapel retrieved', [
            'count' => $mapels->count(),
            'class_id' => $request->query('class_id'),
            'fst_id' => $request->query('fst_id'),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $mapels,
        ]);
    }
}
