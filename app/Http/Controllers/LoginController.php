<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function show()
    {
        if (session('user_name')) {
            return redirect()->route('notes.index');
        }
        return view('login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required|min:2|max:30'
        ]);

        session(['user_name' => trim($request->user_name)]);

        return redirect()->route('notes.index');
    }

    public function logout()
    {
        session()->forget('user_name');
        return redirect()->route('login');
    }
}