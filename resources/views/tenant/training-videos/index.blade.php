@extends('layouts.admin')

@section('contenido')
<style>
    /* Academia de Entrenamiento - Estética SaaS */
    .training-wrapper {
        padding: 20px;
        background: #f4f7fa;
        min-height: 100vh;
    }

    .module-header {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Tarjetas de Video */
    .video-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04);
        background: #fff;
    }

    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.1);
    }

    /* Thumbnail con Overlay de Play */
    .video-thumbnail-container {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16 / 9;
        background: #212529;
    }

    .video-thumbnail-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .video-card:hover .video-thumbnail-container img {
        transform: scale(1.05);
    }

    .play-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .video-card:hover .play-overlay {
        opacity: 1;
    }

    .play-icon {
        color: #fff;
        font-size: 3rem;
        filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));
    }

    /* Detalles de la tarjeta */
    .video-body {
        padding: 18px;
    }

    .video-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8rem;
    }

    .video-desc {
        font-size: 0.85rem;
        color: #718096;
        margin-bottom: 15px;
    }

    .video-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #a0aec0;
    }

    .duration-badge {
        background: rgba(0, 123, 255, 0.1);
        color: #007bff;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .btn-watch {
        border-radius: 8px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    /* Buscador superior */
    .search-bar-container {
        background: #fff;
        padding: 15px 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
</style>

<div class="training-wrapper">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="font-weight-bold mb-1">Centro de Entrenamiento</h2>
                <p class="text-muted">Domina Facturarg con nuestros tutoriales paso a paso.</p>
            </div>
            <div class="d-none d-md-block">
                <span class="badge badge-primary px-3 py-2" style="border-radius: 20px;">
                    <i class="fas fa-graduation-cap mr-1"></i> Academia Facturarg
                </span>
            </div>
        </div>

        @if($videosByModule->count() > 0)
            @foreach($videosByModule as $moduleName => $videos)
                <div class="module-section mb-5">
                    <div class="module-header">
                        <h4 class="font-weight-bold text-dark m-0">
                            {{ $moduleName }}
                        </h4>
                        <span class="text-muted small font-weight-bold">{{ $videos->count() }} Videos</span>
                    </div>

                    <div class="row">
                        @foreach($videos as $video)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail-container">
                                        @if($video->thumbnail_url)
                                            <img src="{{ Storage::url($video->thumbnail_url) }}" alt="{{ $video->title }}">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white">
                                                <i class="fas fa-play-circle fa-4x opacity-25"></i>
                                            </div>
                                        @endif
                                        <div class="play-overlay">
                                            <i class="fas fa-play-circle play-icon"></i>
                                        </div>
                                    </div>

                                    <div class="video-body">
                                        <div class="video-meta mb-2">
                                            @if($video->duration)
                                                <span class="duration-badge">
                                                    <i class="fas fa-clock mr-1"></i>{{ $video->duration }} min
                                                </span>
                                            @endif
                                            <span><i class="fas fa-eye mr-1"></i> Tutorial</span>
                                        </div>
                                        
                                        <h5 class="video-title" title="{{ $video->title }}">
                                            {{ $video->title }}
                                        </h5>
                                        
                                        <p class="video-desc">
                                            {{ Str::limit($video->description, 70) }}
                                        </p>

                                        <a href="{{ route('tenant.training-videos.show', $video->id) }}" 
                                           class="btn btn-primary btn-block btn-watch py-2">
                                            Comenzar Entrenamiento
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <img src="https://cdn-icons-png.flaticon.com/512/7431/7431313.png" width="120" class="mb-4 opacity-50">
                <h4 class="font-weight-bold">Aún no hay videos aquí</h4>
                <p class="text-muted">Estamos preparando el material para que seas un experto.</p>
            </div>
        @endif
    </div>
</div>
@endsection