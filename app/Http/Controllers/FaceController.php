<?php

namespace App\Http\Controllers;

use App\Models\Face;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Response(Face::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $path = $request->file('file')->store('faces');
        $face = Face::create([
            'real_name' => $request->has('real_name')?$request->input('real_name'):null,
            'path' => $path,
            'user_id' => null
        ]);
        return Response($face);
    }
}
