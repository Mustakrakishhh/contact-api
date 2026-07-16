<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ContactRepository;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller
{
    public function index(ContactRepository $repository): JsonResponse
    {
        return response()->json($repository->getStats());
    }
}
