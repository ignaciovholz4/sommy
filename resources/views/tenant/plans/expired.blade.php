<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige un nuevo plan</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Header con fondo degradado */
        .dg-header-top {
            background: radial-gradient(circle at 50% 120%, #1a365d 0%, #172554 40%, #0f172a 100%);
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        .dg-logo {
            height: 90px;
            width: auto;
            display: block;
        }

        /* Contenedor principal */
        main.container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #172554;
            font-weight: 700;
        }

        /* Cards de planes */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            background: #f1f5f9;
            font-weight: 600;
            text-align: center;
        }

        .btn-plan {
            background-color: #007bff;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px;
            transition: background-color 0.2s ease;
        }

        .btn-plan:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header class="dg-header-top">
        <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" class="dg-logo" alt="Sommy" style="height:44px;width:auto;">
    </header>

    <main class="container">
        <h2>Elige un nuevo plan</h2>
        <div class="row justify-content-center">
            @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $plan->name }}</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="card-text text-muted">
                            {{ $plan->features['nota'] ?? 'Sin descripción' }}
                        </p>
                        <ul class="list-unstyled mb-3">
                            <li><strong>Precio:</strong> ${{ number_format($plan->price,2) }}</li>
                            <li><strong>Duración:</strong> {{ $plan->duration_days }} días</li>
                        </ul>
                        <form action="{{ route('tenant.portal.plans.request', $plan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-plan w-100">
                                Solicitar este plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </main>
</body>
</html>