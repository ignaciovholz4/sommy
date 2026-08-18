<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TrainingVideo;
use Illuminate\Http\Request;

class TrainingVideoController extends Controller
{
    public function index()
    {
        // Get all active videos grouped by module
        $videosByModule = TrainingVideo::active()
            ->orderBy('module_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module_name');

        return view('tenant.training-videos.index', compact('videosByModule'));
    }

    public function show($id)
    {
        $video = TrainingVideo::active()->findOrFail($id);

        return view('tenant.training-videos.show', compact('video'));
    }

    public function getVideosByModule($module)
    {
        $videos = TrainingVideo::active()
            ->byModule($module)
            ->orderBy('sort_order')
            ->get();

        return response()->json($videos);
    }
}
