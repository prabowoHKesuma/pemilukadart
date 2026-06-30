<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? $appName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --sidebar-width: 280px;
            --desktop-navbar-height: 56px;
            --border-color: #dee2e6;
            --page-bg: #f5f6f8;
            --sidebar-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --primary: #0d6efd;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text-main);
        }

        .desktop-navbar {
            min-height: var(--desktop-navbar-height);
            display: none;
        }

        .desktop-navbar .navbar-brand {
            font-weight: 700;
        }

        .desktop-user-info {
            color: #ffffff;
            font-size: 0.875rem;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        .app-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1.5rem;
        }

        .mobile-topbar {
            display: none;
        }

        .menu-toggle {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            background: #ffffff;
            font-weight: 600;
        }

        .menu-toggle:hover {
            background: #f8f9fa;
        }

        .menu-toggle.active-parent {
            background: #e9ecef;
            color: #111827;
        }

        .menu-child {
            padding-left: 2rem !important;
            font-size: 0.95rem;
            background: #ffffff;
        }

        .menu-child.active {
            background: var(--primary) !important;
            color: #ffffff !important;
        }

        .menu-arrow {
            font-size: 0.85rem;
            opacity: 0.75;
        }

        .offcanvas-sidebar {
            width: var(--sidebar-width) !important;
        }

        .offcanvas-user {
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .flash-wrapper {
            margin-bottom: 1rem;
        }

        @media (min-width: 992px) {
            .desktop-navbar {
                display: flex;
            }

            .app-shell {
                min-height: calc(100vh - var(--desktop-navbar-height));
            }

            .app-sidebar {
                min-height: calc(100vh - var(--desktop-navbar-height));
            }
        }

        @media (max-width: 991.98px) {
            .app-shell {
                display: block;
                min-height: auto;
            }

            .app-sidebar-desktop {
                display: none !important;
            }

            .mobile-topbar {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 1030;
                background: #ffffff;
                border-bottom: 1px solid var(--border-color);
                padding: 0.65rem 0.75rem;
                align-items: center;
                gap: 0.75rem;
            }

            .mobile-brand {
                flex: 1;
                min-width: 0;
                font-weight: 700;
                font-size: 0.95rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mobile-logout-form {
                flex-shrink: 0;
            }

            .app-content {
                padding: 1rem;
            }
        }
    </style>
</head>