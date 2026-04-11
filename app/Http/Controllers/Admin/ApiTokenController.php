<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tieneToken = !is_null(auth()->user()->api_token);
        return view('admin.api', compact('tieneToken'));
    }

    public function generate()
    {
        $plainToken = \Illuminate\Support\Str::random(60);
        auth()->user()->update(['api_token' => hash('sha256', $plainToken)]);

        return back()->with('token', $plainToken);
    }

    public function revoke()
    {
        auth()->user()->update(['api_token' => null]);
        return back()->with('success', 'Token revocado correctamente.');
    }
}
