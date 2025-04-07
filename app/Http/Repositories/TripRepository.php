<?php

namespace App\Http\Repositories;

use App\Models\Trip;
use App\Helpers\FilterHandler;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TripRepository
{
    public function index($paginate, $filters, $orderBy)
    {
        $query = Trip::query();
        $filterHandler = new FilterHandler;

        $query = $filterHandler->applyFilter($query, $filters);
        $query = $filterHandler->applyOrder($query, $orderBy);

        return $paginate ? $query->paginate($paginate) : $query->limit(100)->get();
    }

    public function show($id)
    {
        return Trip::findOrFail($id);
    }

    public function store($data)
    {
        return Trip::create($data);
    }

    public function update($id,$data)
    {
        $trip = $this->show($id);
        $trip->update($data);
        return $trip;
    }

    public function updateStatus($id, $status)
    {
        $trip = $this->show($id);
        $trip->update(['status' => $status]);
        return $trip;
    }
}
