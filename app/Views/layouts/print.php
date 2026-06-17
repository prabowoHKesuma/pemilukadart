<?php

use App\Core\Env;

$appName = Env::get('APP_NAME', 'RT Voting');

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? $appName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 24px;
            font-size: 13px;
        }

        h1, h2, h3 {
            margin: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #666;
        }

        .mb-1 {
            margin-bottom: 6px;
        }

        .mb-2 {
            margin-bottom: 12px;
        }

        .mb-3 {
            margin-bottom: 18px;
        }

        .mt-3 {
            margin-top: 18px;
        }

        .border-box {
            border: 1px solid #333;
            padding: 12px;
        }

        .info-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #333;
            padding: 7px 8px;
            vertical-align: top;
        }

        .data-table th {
            background: #f0f0f0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 32px;
        }

        .signature-table td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 8px;
        }

        .signature-space {
            height: 70px;
        }

        .no-print {
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            background: #111;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .btn-secondary {
            background: #666;
        }

        .warning {
            border: 1px solid #b00020;
            color: #b00020;
            padding: 10px;
            margin-bottom: 12px;
        }

        @media print {
            body {
                margin: 0;
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 14mm;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn">
        Print / Save PDF
    </button>

    <button onclick="window.close()" class="btn btn-secondary">
        Tutup
    </button>
</div>

<?= $content ?>

</body>
</html>