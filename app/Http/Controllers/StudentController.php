<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function show(Request $request): Response
    {
        $student = DB::select("
            select st.id, st.student_no, st.name, st.school, concat(s.name,' (',s.shortcut,')') as school_name, st.college, concat(c.name,' (',c.shortcut,')') as college_name, st.hall, h.name as hall_name from students st
            inner join schools s on st.school = s.id
            inner join colleges c on s.college = c.id
            inner join halls h on st.hall = h.id
            where st.student_no = ".$request->user()->student_no.";"
        );
        return Response((array) $student[0]);
    }
}
