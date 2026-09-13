<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class DataTableHelper
{
    /**
     * Process a query into a DataTables server-side JSON response.
     *
     * @param QueryBuilder|EloquentBuilder $query
     * @param Request $request
     * @param array $searchableColumns Columns searched by DataTables search box
     * @param array $orderableColumns Map of column index => DB column name
     * @param string $countField Field to count (default '*')
     * @param \Closure|null $transformer Optional callback to transform each record
     * @return \Illuminate\Http\JsonResponse
     */
    public static function process(
        $query,
        Request $request,
        array $searchableColumns = [],
        array $orderableColumns = [],
        string $countField = '*',
        ?\Closure $transformer = null,
        array $extra = []
    ) {
        // If not a server-side request (draw param missing), return standard format
        if (!$request->has('draw')) {
            $allData = $query->get();
            if ($transformer) {
                $allData = $allData->map($transformer);
            }
            return response()->json(['data' => $allData]);
        }

        // 1. Total records before any DataTables search
        $totalRecords = (clone $query)->count($countField);

        // 2. Global search filter
        $searchValue = trim($request->input('search.value', ''));
        if ($searchValue !== '' && !empty($searchableColumns)) {
            $query->where(function ($q) use ($searchableColumns, $searchValue) {
                foreach ($searchableColumns as $i => $column) {
                    if ($i === 0) {
                        $q->where($column, 'like', "%{$searchValue}%");
                    } else {
                        $q->orWhere($column, 'like', "%{$searchValue}%");
                    }
                }
            });
        }

        // 3. Total filtered records
        $filteredRecords = (clone $query)->count($countField);

        // 4. Ordering
        $orderColIndex = $request->input('order.0.column');
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($orderColIndex !== null && isset($orderableColumns[$orderColIndex])) {
            $query->orderBy($orderableColumns[$orderColIndex], $orderDir);
        }

        // 5. Pagination
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->get();

        if ($transformer) {
            $data = $data->map($transformer);
        }

        return response()->json(array_merge([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ], $extra));
    }
}
