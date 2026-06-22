<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Core/Env.php';
require_once __DIR__ . '/app/Core/Session.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Core/Controller.php';
require_once __DIR__ . '/app/Core/View.php';
require_once __DIR__ . '/app/Core/Redirect.php';
require_once __DIR__ . '/app/Core/Csrf.php';
require_once __DIR__ . '/app/Core/Auth.php';
require_once __DIR__ . '/app/Core/RegionScope.php';
require_once __DIR__ . '/app/Core/MenuPresenter.php';
require_once __DIR__ . '/app/Core/LayoutData.php';
require_once __DIR__ . '/app/Core/ViewFormatter.php';

use App\Core\Env;
use App\Core\Session;

Env::load(__DIR__ . '/.env');

date_default_timezone_set('Asia/Jakarta');

Session::start();