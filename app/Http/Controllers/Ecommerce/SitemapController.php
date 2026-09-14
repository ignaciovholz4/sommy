<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Support\Facades\DB;

/**
 * Sitemap XML dinámico para SEO: home, categorías y productos activos con
 * su fecha de última modificación. Referenciado desde public/robots.txt.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias')->where('status', 1)->get(['slug', 'nombre']);

        $productos = Articulo::where('estado', 'Activo')
            ->whereNotNull('slug')
            ->get(['slug']);

        $urls = collect();

        $urls->push(['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => url('/productos'), 'priority' => '0.9', 'changefreq' => 'daily']);

        foreach ($categorias as $cat) {
            if ($cat->slug) {
                $urls->push(['loc' => url('/categoria/' . $cat->slug), 'priority' => '0.8', 'changefreq' => 'daily']);
            }
        }

        foreach ($productos as $p) {
            $urls->push(['loc' => url('/producto/' . $p->slug), 'priority' => '0.7', 'changefreq' => 'weekly']);
        }

        $xml = view('ecommerce.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
