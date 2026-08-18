@extends('layouts.admin')

@section('title', 'Carga Masiva de Productos')

@section('contenido')
<style>
    /* ADN Visual Facturarg - Estilo Premium */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f1f5f9;      
        --facturarg-accent: #22d3ee;  
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 3rem 2.5rem;
    }

    .card-facturarg {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        background: #fff;
        overflow: hidden;
    }

    /* Padding interno reforzado */
    .card-body-large {
        padding: 3rem !important; 
    }

    /* Banner de Descarga Resaltado */
    .banner-download {
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 2rem 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: 0.3s;
    }

    .banner-download:hover {
        border-color: var(--facturarg-cyan);
        background-color: #f0f9ff;
    }

    /* Botón estilo Facturarg */
    .btn-facturarg-dark {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 10px;
        padding: 15px 30px;
        font-weight: 700;
        border: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .btn-facturarg-dark:hover {
        background-color: #1e293b;
        color: var(--facturarg-accent);
        transform: translateY(-2px);
    }

    /* Zona de carga */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 60px 40px;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #fcfcfc;
        text-align: center;
    }

    .upload-zone:hover {
        border-color: var(--facturarg-cyan);
        background: #f0f9ff;
    }

    .step-badge {
        width: 38px;
        height: 38px;
        background: var(--facturarg-dark);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .badge-cat {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .stat-card-inner {
        padding: 2.5rem;
        border-radius: 15px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1">Carga Masiva</h1>
            <p class="text-muted mb-0">Importación de stock y catálogo mediante archivos maestros.</p>
        </div>
        <a href="{{ route('articulo') }}" class="btn btn-outline-secondary px-4 fw-bold" style="border-radius: 10px;">
            <i class="fas fa-arrow-left me-2"></i> VOLVER
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-facturarg mb-4">
                <div class="card-body-large">
                    
                    <div class="banner-download mb-5">
                        <div class="d-flex align-items-center">
                            <div class="step-badge me-4" style="background-color: var(--facturarg-cyan); font-size: 1.2rem;">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold m-0 text-dark">¿No tenés la plantilla oficial?</h4>
                                <p class="text-muted mb-0">Descargala ahora para asegurar que los datos tengan el formato correcto.</p>
                            </div>
                        </div>
                        <a href="{{ route('download_template') }}" class="btn-facturarg-dark bg-info text-white">
                            <i class="fas fa-download me-2"></i> DESCARGAR
                        </a>
                    </div>

                    <form id="bulkUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="step-badge me-3">1</div>
                                    <h4 class="fw-bold m-0 text-dark">Selección de Archivo</h4>
                                </div>
                                <label for="file" class="upload-zone d-block w-100" id="drop-area">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                                    <h5 class="fw-bold mb-2" id="file-name-display">Subí tu Excel o CSV aquí</h5>
                                    <p class="text-muted small">Arrastrá el archivo o hacé clic para explorar</p>
                                    <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" hidden required>
                                </label>
                            </div>

                            <div class="col-md-12">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="step-badge me-3">2</div>
                                    <h4 class="fw-bold m-0 text-dark">Confirmar Importación</h4>
                                </div>
                                <div class="bg-light p-4 rounded-4 border-start border-info border-4 mb-4" style="padding: 2rem !important;">
                                    <p class="text-dark fw-bold mb-0">
                                        <i class="fas fa-info-circle me-2 text-info"></i> 
                                        Al iniciar, el sistema procesará cada fila. Los errores se detallarán al finalizar.
                                    </p>
                                </div>
                                <button type="button" id="uploadBtn" class="btn-facturarg-dark w-100 justify-content-center py-3">
                                    <i class="fas fa-rocket me-2"></i> INICIAR PROCESAMIENTO MASIVO
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="uploadProgress" class="mt-5" style="display: none;">
                        <div class="progress" style="height: 12px; border-radius: 20px; background: #e2e8f0;">
                            <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; border-radius: 20px;"></div>
                        </div>
                        <p class="text-center fw-bold small text-muted mt-3 text-uppercase">Analizando registros y validando stock...</p>
                    </div>
                </div>
            </div>

            <div id="uploadResults" style="display: none;">
                <div class="card-facturarg card-body-large mt-4">
                    <h5 class="fw-bold mb-4 text-uppercase">Resumen de la Operación</h5>
                    <div id="resultsContent"></div>
                    
                    <div id="errorDetails" class="mt-5" style="display: none;">
                        <div class="p-4 rounded-4" style="background-color: #fff1f2; border: 1px solid #fecdd3; padding: 2.5rem !important;">
                            <h6 class="text-danger fw-bold mb-3"><i class="fas fa-times-circle me-2"></i> DETALLE DE FILAS RECHAZADAS</h6>
                            <div id="errorContent" class="small" style="max-height: 300px; overflow-y: auto; color: #9f1239;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-facturarg card-body-large shadow-sm">
                <label class="small fw-bold text-muted text-uppercase mb-4 d-block" style="letter-spacing: 1px;">Categorías Activas</label>
                <div class="d-flex flex-wrap gap-2 mb-5">
                    @foreach($categoria as $cat)
                        <span class="badge badge-cat">{{ $cat->nombre }}</span>
                    @endforeach
                </div>
                
                <hr class="my-5 opacity-25">
                
                <label class="small fw-bold text-info text-uppercase mb-3 d-block">Reglas de Oro</label>
                <ul class="list-unstyled">
                    <li class="mb-4 d-flex align-items-start small text-muted">
                        <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                        <span><strong>CÓDIGOS:</strong> No pueden repetirse en el sistema.</span>
                    </li>
                    <li class="mb-4 d-flex align-items-start small text-muted">
                        <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                        <span><strong>FORMATO IVA:</strong> Escribir "IVA 21%" o "IVA 10,5%".</span>
                    </li>
                    <li class="d-flex align-items-start small text-muted">
                        <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                        <span><strong>TIPO PRODUCTO:</strong> "1" (Simple), "2" (Combo/Kit).</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Cambio visual del input
    $('#file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#file-name-display').html('<span class="text-info fw-bold">' + fileName + '</span>');
        $('#drop-area').css('border-color', '#1591a3').css('background', '#f0f9ff');
    });

    $('#uploadBtn').on('click', function(e) {
        e.preventDefault();
        var formData = new FormData($('#bulkUploadForm')[0]);
        
        if (!$('#file')[0].files[0]) {
            Swal.fire({ title: 'Atención', text: 'Por favor, cargá un archivo primero.', icon: 'warning', confirmButtonColor: '#0f172a' });
            return;
        }

        $('#uploadProgress').show();
        $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-sync fa-spin me-2"></i> PROCESANDO ARCHIVO...');
        $('#uploadResults, #errorDetails').hide();

        $.ajax({
            url: '{{ route("process_bulk_upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        $('.progress-bar').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $('#uploadProgress').hide();
                $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-rocket me-2"></i> INICIAR PROCESAMIENTO MASIVO');
                if (response.success) { 
                    showResults(response); 
                } else { 
                    Swal.fire('Error de Sistema', response.message, 'error'); 
                }
            },
            error: function() {
                $('#uploadProgress').hide();
                $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-rocket me-2"></i> INICIAR PROCESAMIENTO MASIVO');
                Swal.fire('Error Crítico', 'Verificá que la extensión ZipArchive esté activa en el servidor.', 'error');
            }
        });
    });

    function showResults(response) {
        var stats = response.statistics;
        var html = `
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-card-inner border-success">
                        <h2 class="fw-bold text-success mb-1">${stats.success_count}</h2>
                        <p class="small text-muted fw-bold text-uppercase m-0">Productos creados</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card-inner border-danger">
                        <h2 class="fw-bold text-danger mb-1">${stats.error_count}</h2>
                        <p class="small text-muted fw-bold text-uppercase m-0">Filas omitidas</p>
                    </div>
                </div>
            </div>`;
        
        $('#resultsContent').html(html);
        $('#uploadResults').fadeIn();

        if (stats.errors && stats.errors.length > 0) {
            var errorHtml = '<ul class="mb-0 ps-3">';
            stats.errors.forEach(function(error) { errorHtml += '<li class="mb-2">' + error + '</li>'; });
            errorHtml += '</ul>';
            $('#errorContent').html(errorHtml);
            $('#errorDetails').show();
        }
        Swal.fire({ title: 'Carga Finalizada', text: 'El proceso terminó de analizar el archivo.', icon: 'success', confirmButtonColor: '#0f172a' });
    }
});
</script>
@endsection