<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WaTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'whatsapp.tags.store');

        $request->validate([
            'nombre' => 'required|string|max:60|unique:wa_tags,nombre',
            'color' => 'nullable|string|max:20',
        ]);

        $tag = WaTag::create([
            'nombre' => $request->nombre,
            'color' => $request->color ?: '#6c757d',
        ]);

        return response()->json(['status' => 1, 'tag' => $tag]);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'whatsapp.tags.destroy');

        WaTag::findOrFail($id)->delete();

        return response()->json(['status' => 1]);
    }
}
