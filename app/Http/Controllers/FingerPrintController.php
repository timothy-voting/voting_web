<?php

namespace App\Http\Controllers;

use App\Models\FingerPrint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FingerPrintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Response(FingerPrint::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $path = $request->file('file')->store('fingerprints');
        $fingerprint = FingerPrint::create([
            'real_name' => $request->has('real_name')?$request->input('real_name'):null,
            'path' => $path,
            'user_id' => null
        ]);
        return Response($fingerprint);
    }
}
