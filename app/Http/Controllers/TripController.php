<?php

namespace App\Http\Controllers;

use App\Http\Requests\TripRequest;
use App\Http\Requests\TripStatusRequest;
use App\Http\Services\TripService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request)
    {
        try {
            $trips = $this->tripService->index($request['paginate'], $request['filters'], $request['orderBy']);

            return response()->json(['data' => $trips], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(TripRequest $request)
    {
        DB::beginTransaction();

        try {
            $trip = $this->tripService->store($request->validated());
            DB::commit();

            return response()->json(['data' => $trip], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $trip = $this->tripService->show($id);
            return response()->json(['data' => $trip], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(TripRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $trip = $this->tripService->update($id, $request->validated());
            DB::commit();

            return response()->json(['data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(TripStatusRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $trip = $this->tripService->updateStatus($id, $request->validated('status'));
            DB::commit();

            return response()->json(['data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
