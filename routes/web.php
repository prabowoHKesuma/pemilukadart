<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElectionController;
use App\Controllers\CandidateController;
use App\Controllers\VoterController;
use App\Controllers\ElectionVoterController;
use App\Controllers\VotingController;
use App\Controllers\ResultController;
use App\Controllers\AuditLogController;
use App\Controllers\RemoteVerificationController;

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

$router->get('/voters', [VoterController::class, 'index']);
$router->get('/voters/create', [VoterController::class, 'create']);
$router->post('/voters/store', [VoterController::class, 'store']);
$router->get('/voters/{id}/edit', [VoterController::class, 'edit']);
$router->post('/voters/{id}/update', [VoterController::class, 'update']);
$router->post('/voters/{id}/delete', [VoterController::class, 'destroy']);

$router->get('/elections/{electionId}/voters', [ElectionVoterController::class, 'index']);
$router->get('/elections/{electionId}/voters/create', [ElectionVoterController::class, 'create']);
$router->post('/elections/{electionId}/voters/store', [ElectionVoterController::class, 'store']);
$router->post('/elections/{electionId}/voters/{id}/channel', [ElectionVoterController::class, 'updateChannel']);
$router->post('/elections/{electionId}/voters/{id}/delete', [ElectionVoterController::class, 'destroy']);

$router->get('/tps-voting', [VotingController::class, 'index']);
$router->get('/elections/{electionId}/tps-voting', [VotingController::class, 'searchVoter']);
$router->get('/elections/{electionId}/tps-voting/success', [VotingController::class, 'success']);
$router->get('/elections/{electionId}/tps-voting/{electionVoterId}/ballot', [VotingController::class, 'ballot']);
$router->post('/elections/{electionId}/tps-voting/{electionVoterId}/submit', [VotingController::class, 'submit']);

$router->get('/results', [ResultController::class, 'index']);
$router->get('/elections/{electionId}/results', [ResultController::class, 'show']);

$router->get('/audit-logs', [AuditLogController::class, 'index']);

$router->get('/remote-verifications', [RemoteVerificationController::class, 'index']);
$router->get('/elections/{electionId}/remote-verifications', [RemoteVerificationController::class, 'election']);
$router->get('/elections/{electionId}/remote-verifications/create', [RemoteVerificationController::class, 'create']);
$router->post('/elections/{electionId}/remote-verifications/store', [RemoteVerificationController::class, 'store']);
$router->get('/elections/{electionId}/remote-verifications/{id}', [RemoteVerificationController::class, 'show']);
$router->post('/elections/{electionId}/remote-verifications/{id}/upload', [RemoteVerificationController::class, 'upload']);
$router->post('/elections/{electionId}/remote-verifications/{id}/approve', [RemoteVerificationController::class, 'approve']);
$router->post('/elections/{electionId}/remote-verifications/{id}/reject', [RemoteVerificationController::class, 'reject']);
$router->get('/remote-verifications/{id}/file/{type}', [RemoteVerificationController::class, 'file']);