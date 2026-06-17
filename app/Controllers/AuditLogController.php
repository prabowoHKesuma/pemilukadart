<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['superadmin']);

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'action' => trim($_GET['action'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];

        $logs = AuditLog::all($filters);
        $actions = AuditLog::actions();

        $this->view('audit_logs/index', [
            'title' => 'Audit Log',
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $filters,
        ]);
    }
}