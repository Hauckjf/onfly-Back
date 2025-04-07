<?php

namespace App\Http\Services;

use App\Http\Repositories\TripRepository;
use Illuminate\Support\Facades\Auth;

class TripService
{
    protected $tripRepository;

    public function __construct(TripRepository $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function index($paginate, $filters, $orderBy)
    {
        return $this->tripRepository->index($paginate, $filters, $orderBy);
    }

    public function show($id)
    {
        return $this->tripRepository->show($id);
    }

    public function store($data)
    {
        $data['user_id'] = Auth::id();

        return $this->tripRepository->store($data);
    }

    public function update($id, $data)
    {
        return $this->tripRepository->update($id, $data);
    }

    public function updateStatus($id, $status)
    {
        return $this->tripRepository->updateStatus($id, ['status' => $status]);
    }


}
