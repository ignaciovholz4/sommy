<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Visor de auditoria: quien hizo que, cuando y desde donde. Alimentado por
 * App\Http\Middleware\AuditLog (registra toda request que modifica datos).
 */
class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('haveaccess', 'admin.auditoria.index');

        $usuarios = User::where('estatus', 1)->orderBy('name')->get(['id', 'name']);

        return view('admin.auditoria.index', compact('usuarios'));
    }

    public function data(Request $request)
    {
        Gate::authorize('haveaccess', 'admin.auditoria.index');

        $logs = AuditLog::with('user')
            ->when($request->query('user_id'), fn ($q) => $q->where('user_id', $request->query('user_id')))
            ->when($request->query('ruta'), fn ($q) => $q->where('ruta', 'like', '%' . $request->query('ruta') . '%'))
            ->when($request->query('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('desde')))
            ->when($request->query('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('hasta')))
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'estado' => 1,
            'data' => $logs->map(fn (AuditLog $l) => [
                'id' => $l->id,
                'fecha' => $l->created_at->format('d/m/Y H:i:s'),
                'usuario' => optional($l->user)->name ?? '—',
                'metodo' => $l->metodo,
                'ruta' => $l->ruta ?: '—',
                'url' => $l->url,
                'ip' => $l->ip,
                'status' => $l->status,
                'payload' => $l->payload,
            ])->values(),
            'paginacion' => [
                'pagina' => $logs->currentPage(),
                'ultima_pagina' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
