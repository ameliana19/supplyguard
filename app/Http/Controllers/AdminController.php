<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display the Admin Panel or User Profile depending on the role.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Memastikan hanya user yang terautentikasi yang dapat mengakses
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Mengembalikan tampilan panel. Hak akses UI ditangani langsung di blade 
        // dengan mengecek Auth::user()->role.
        return view('admin.panel');
    }
}
