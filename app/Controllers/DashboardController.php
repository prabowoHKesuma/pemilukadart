<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $this->view('dashboard/index', [
            'title' => 'Dashboard'
        ]);
    }
}