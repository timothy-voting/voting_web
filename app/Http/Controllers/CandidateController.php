<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    //expects affiliation = ['university','college','school','hall']
    public function show(Request $request): Response
    {
        $aff = $request->input('affiliation');
        $query = "select ca.id, s.name from candidates ca
                  inner join students s on ca.student_id = s.id
                  where ca.position=" . $request->input('position') .
            (($aff=="university")?";":" and s." . $aff . "=" . $request->input('affiliation_id') . ";");
        return Response(DB::select($query));
    }
}
