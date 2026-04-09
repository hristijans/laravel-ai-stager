<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;

class StagerDashboardController extends Controller
{
    public function index(): View
    {
        return view('ai-stager::stager.dashboard');
    }

    public function logs(): View
    {
        return view('ai-stager::stager.logs');
    }
}
