<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingVideo;
use Illuminate\Http\Request;

class UserdashboardController extends Controller
{
    public function index()
    {
        // Get all active training videos grouped by module
        $videosByModule = TrainingVideo::active()
            ->orderBy('module_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module_name');

        return view('dashboard_user.index', compact('videosByModule'));
    }
}
