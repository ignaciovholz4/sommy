<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    public static function getByCategory($id)
    {
        $getDataCategory = DB::table('categorias')->where('idcategoria','=', $id)->get();
        return $getDataCategory;
    }

    public static function getAllCategory()
    {
        $getAllCategory = DB::table('categorias')->where('status','=',1)->orderBy('orden')->orderBy('nombre')->get();
        return $getAllCategory;
    }

    public static function getEmpresaImage()
    {
        $empresa = DB::table('configuracion')->first();
        $dataEmpresa = [
            'name'   => $empresa->name ?? 'FacturARG',
            'image'  => $empresa ? "imagenes/empresa/".$empresa->image : 'imagenes/empresa/default.png',
            'adress' => $empresa->adress ?? '',
            'email'  => $empresa->email ?? '',
            'phone'  => $empresa->phone ?? '',
            // Numero para links de WhatsApp en formato internacional (549...):
            // si no esta cargado, cae al telefono comun.
            'whatsapp' => ($empresa->whatsapp ?? '') ?: ($empresa->phone ?? ''),
        ];
        return $dataEmpresa;
    }

    public static function getLimitCategory()
    {
        $getDataCategoryLimit = DB::table('categorias')->where('status', 1)->orderBy('orden')->orderBy('nombre')->take(7)->get();
        return $getDataCategoryLimit;
    }

}
