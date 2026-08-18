
@extends ('layouts.admin')
@section ('contenido')
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Bienvenido al sistema</h5>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Si necesita ayuda. Por favor comunicarse con el administrador del sistema</h5>
                    <p class="card-text"></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-video"></i> Videos de Entrenamiento</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($videosByModule->count() > 0)
                        @foreach($videosByModule as $moduleName => $videos)
                            <div class="mb-3">
                                <h6 class="text-primary">
                                    <i class="fas fa-folder"></i> {{ $moduleName }}
                                </h6>
                                <ul class="list-unstyled">
                                    @foreach($videos as $video)
                                        <li class="mb-2">
                                            <a href="{{ route('tenant.training-videos.show', $video->id) }}"
                                               class="text-decoration-none d-block"
                                               target="_blank">
                                                <i class="fas fa-play-circle text-success"></i>
                                                {{ $video->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        <div class="text-center mt-3">
                            <a href="{{ route('tenant.training-videos.index') }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> Ver todos los videos
                            </a>
                        </div>
                    @else
                        <p class="text-muted">No hay videos de entrenamiento disponibles.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
