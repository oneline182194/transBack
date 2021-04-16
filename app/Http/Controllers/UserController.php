<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = User::get();
        echo json_encode($usuarios);
//        echo 'Hello';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       print_r($request->all());
//        $usuario = new User();
//        $usuario->dni = $request->input('dni');
//        $usuario->user = $request->input('user');
//        $usuario->password = $request->input('password');
//        $usuario->password = $request->input('password');
//        $usuario->lastSession = $request->input('lastSession');
//        $usuario->estado = $request->input('estado');
//        $usuario->save();

//        echo json_encode($usuario);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
       //
    }
}
