<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $data['logo'] = '/pemilukadart/public/assets/images/logo_pilkadart11.png';

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'logo' => $data['logo'] 
        ]);
    }
}