<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Face;
use App\Models\FingerPrint;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthenticatedSessionController extends Controller
{
	/**
	 * Display the login view.
	 *
	 * @return \Inertia\Response
	 */
	public function create(): \Inertia\Response
	{
		return Inertia::render('Auth/Login', [
			'canResetPassword' => Route::has('password.request'),
			'status' => session('status'),
		]);
	}

	/**
	 * Handle an incoming authentication request.
	 *
	 * @param LoginRequest $request
	 * @return RedirectResponse
	 * @throws ValidationException
	 */

	public function store(LoginRequest $request): RedirectResponse
	{
		$request->authenticate();

		$request->session()->regenerate();

		return redirect()->intended(RouteServiceProvider::HOME);
	}

	/**
	 * @throws ValidationException
	 */
	public function apiStore(LoginRequest $request): Response | StreamedResponse
	{
		$request->authenticate();
		$user = $request->user();
		if($request->has('finger_details') || $request->hasFile('face')) {
			$bioAuth = false;
			$storage_path = storage_path().'/app/';
			if ($request->has('finger_details')) {
				$bioAuth = false;
			} elseif ($request->hasFile('face')) {
				$path = $storage_path.$request->file('face')->store('temp_faces');
				$face = Face::where('user_id', $user->id)->first();
				$face_path = $storage_path.$face->path;
				$socket = stream_socket_client('tcp://127.0.0.1:8001', $errno, $errstr, 30);
				if ($socket) {
					stream_socket_sendto($socket, $face_path.",".$path);
					$bioAuth = (stream_get_contents($socket)=='true');
				}
			}

			if($bioAuth){
        $token = $user->createToken('login');
				return Response(['token' => $token->plainTextToken]);
			}
			$error = [
				"message" => "Failed to authenticate biometricData.",
				"errors" => [
					"biometricData" => [
						"Failed to authenticate biometricData."
					]
				]
			];
			return Response($error, 422);
		}

		$fingerprint = FingerPrint::where('user_id', $user->id)->first();
		if($fingerprint == null){
			$error = [
				"message" => "You do not have biometric data.",
				"errors" => [
					"biometricData" => [
						"You do not have biometric data."
					]
				]
			];
			return Response($error, 422);
		}

		return Storage::download($fingerprint->path, 'fingerprint.bin');
	}

	/**
	 * Destroy an authenticated session.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return redirect('/login');
	}

	public function apiDestroy(Request $request): Response
	{
		$request->user()->currentAccessToken()->delete();
		return Response(['token'=>null]);
	}
}
