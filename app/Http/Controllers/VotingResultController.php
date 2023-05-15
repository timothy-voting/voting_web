<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VotingResultController extends Controller
{
    public function index(Request $request): Response
    {
        if($request->ip() != '127.0.0.1'){
            return Response('Not Authorized!');
        }
        $candidates = DB::select("
            select distinct ca.id as candidate_id, p.id as position_id, p.name as position, p.affiliation, s.id as student_id, s.name as student, h.id as hall_id, sc.id as school_id, c.id as college_id, ca.votes as votes, c.name as college, sc.name as school, h.name as hall
            from candidates ca inner join positions p on ca.position = p.id inner join students s on ca.student_id = s.id
            inner join colleges c on s.college = c.id inner join schools sc on s.school = sc.id
            inner join halls h on s.hall = h.id order by p.id, s.id;
        ");

        $cands = [];
        $votes = [];
        foreach ($candidates as $candidate){
            $cands[$candidate->candidate_id] = $candidate;
            $votes[$candidate->candidate_id] = [$candidate->votes];
        }
        return Response(['candidates'=>$cands, 'votes'=>$votes]);
    }


    public function store(Request $request): Response
    {
        if($request->ip() != '127.0.0.1'){
            return Response('Not Authorized!');
        }

        $student = Student::where('student_no', '=', $request->user()->student_no)->take(1)->get()[0];
//        if($student->done == 1){
//            return Response('You finished voting!');
//        }

        $voter_uuid = Str::uuid();
        $vote = Vote::create([
            'voter_special_number' => $voter_uuid->toString(),
            'vote' => $request->input('vote')
        ]);

        $votes = json_decode($vote->vote, true);
        $votes_arr = [];

        foreach ($votes as $value){
            $votes_arr = [...$votes_arr, ...$value];
        }

        $upd_str = str_replace(['[', ']'], ['(', ')'], json_encode($votes_arr));

        $student->done = true;
        $student->save();
        $results = DB::update('update candidates set votes=votes+1 where id in '.$upd_str.';');

        return Response(json_encode(['voter_special_number'=>$vote->voter_special_number, 'votes'=>($results>0)?$votes_arr:[]]));
    }
}
