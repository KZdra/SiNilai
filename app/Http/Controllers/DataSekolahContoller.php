<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataSekolahContoller extends Controller
{
    public function index(){
        return view('mdsekolah.index');
    }
}
