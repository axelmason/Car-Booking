<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterRequest $r)
    {
        $validate = $r->validated();
        if ($validate) {
            $create = User::create($validate);
            $token = Hash::make($create->login);
            $edited = str_replace(['/', ',', '.', '?', '$'], '', $token);
            $create->token = $edited;
            $create->save();
            return response()->json($create, 200);
        }
    }

    /**
     * Generates and display api token if user auth was success.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $r)
    {
        if(auth()->attempt(['login' => $r->login, 'password' => $r->password])) {
            $user = Auth::user();
            $user->api_token = Str::random(60);
            $user->save();
            return response()->json(["api_token" => $user->api_token], 200);
        }

        return response()->json([
            'error' => 'Wrong login or password',
            'code' => 401
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(User::find($id), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $r, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
