<?php

namespace App\Http\Repositories;

use App\Models\Trip;
use App\Helpers\FilterHandler;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TripRepository
{
    public function index($paginate, $filters, $orderBy)
    {

        if (!auth()->user()->hasRole('admin')) {
            $query = Trip::where('user_id', Auth::id());
        }
        else
        {
            $query = Trip::query();
        }

        $filterHandler = new FilterHandler;

        $query = $filterHandler->applyFilter($query, $filters);
        $query = $filterHandler->applyOrder($query, $orderBy);

        return $paginate ? $query->paginate($paginate) : $query->limit(100)->get();
    }

    public function show($id)
    {
        if (auth()->user()->hasRole('admin')) {
            return Trip::findOrFail($id);
        } else {
            return Trip::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        }

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
        $trip->update(['status' => $status['status']]);
        return $trip;
    }
}
