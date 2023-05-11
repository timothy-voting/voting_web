<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Face;
use App\Models\FingerPrint;
use App\Models\Student;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function store(Request $request): Response | RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'student_no' => 1,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function apiStore(Request $request): \Illuminate\Http\Response
    {
        $rules = [
            'student_no' => 'required|integer|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'faces' => 'required',
            'fingerprints' => 'required'
        ];

        $messages = [
            'unique' => 'The :attribute :input has already been taken.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response($validator->errors(), 422);
        }

        $students = Student::where('student_no',$request->student_no)->get();
        if(count($students) == 0){
            $validator->errors()->add(
                'student', 'Student number does not exist.'
            );
            return Response($validator->errors(), 422);
        }

        $faceIds = $request->input('faces');
        $faces = [];
        foreach ($faceIds as $faceId){
            $face = Face::find($faceId);
            if($face == null){
                $validator->errors()->add(
                    'face', 'Face does not exist.'
                );
                return Response($validator->errors(), 422);
            }
            $faces[] = $face;
        }

        $fingerprintIds = $request->input('fingerprints');
        $fingerprints = [];
        foreach ($fingerprintIds as $fingerprintId){
            $fingerprint = Fingerprint::find($fingerprintId);
            if($fingerprint == null){
                $validator->errors()->add(
                    'fingerprint', 'fingerprint does not exist.'
                );
                return Response($validator->errors(), 422);
            }
            $fingerprints[] = $fingerprint;
        }

        $user = User::create([
            'name' => $request->student_no,
            'email' => $request->input('student_no').'@evote.com',
            'student_no' => $request->student_no,
            'password' => Hash::make($request->password),
        ]);

        foreach ($faces as $face){
            $face->user_id = $user->id;
            $face->save();
        }

        foreach ($fingerprints as $fingerprint){
            $fingerprint->user_id = $user->id;
            $fingerprint->save();
        }

        return Response($user);
    }

    public function show(): \Illuminate\Http\Response
    {
        return Response(User::all());
    }
}
