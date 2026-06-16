<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElectionController;
use App\Controllers\CandidateController;

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/elections', [ElectionController::class, 'index']);
$router->get('/elections/create', [ElectionController::class, 'create']);
$router->post('/elections/store', [ElectionController::class, 'store']);
$router->get('/elections/{id}/edit', [ElectionController::class, 'edit']);
$router->post('/elections/{id}/update', [ElectionController::class, 'update']);
$router->post('/elections/{id}/delete', [ElectionController::class, 'destroy']);
$router->post('/elections/{id}/status', [ElectionController::class, 'changeStatus']);

$router->get('/elections/{electionId}/candidates', [CandidateController::class, 'index']);
$router->get('/elections/{electionId}/candidates/create', [CandidateController::class, 'create']);
$router->post('/elections/{electionId}/candidates/store', [CandidateController::class, 'store']);
$router->get('/elections/{electionId}/candidates/{id}/edit', [CandidateController::class, 'edit']);
$router->post('/elections/{electionId}/candidates/{id}/update', [CandidateController::class, 'update']);
$router->post('/elections/{electionId}/candidates/{id}/delete', [CandidateController::class, 'destroy']);