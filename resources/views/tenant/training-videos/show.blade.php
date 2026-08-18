@extends('layouts.admin')

@section('contenido')
<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>{{ $video->title }}</h3>
                </div>
                <div class="card-body">
                    @if($video->video_url)
                        <div class="mb-3">
                            <video controls class="w-100" style="max-height: 400px;">
                                <source src="{{ Storage::url($video->video_url) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @endif

                    @if($video->description)
                        <p>{{ $video->description }}</p>
                    @endif

                    @if($video->duration)
                        <p><strong>Duración:</strong> {{ $video->duration }} minutos</p>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('tenant.training-videos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
