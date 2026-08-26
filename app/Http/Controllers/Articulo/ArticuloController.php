<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Articulo;
use App\Models\ProductoAtributo;
use App\Models\ProductoAtributoVariante;
use App\Models\ProductoCombinacion;
use App\Models\ProductoImagen;
use App\Models\SucursalCombinacion;

use Yajra\Datatables\Datatables;
use File;
use Str,Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Imports\ArticulosImport;
use App\Imports\BulkProductImport;
use App\Exports\articuloExport;
use App\Exports\ProductTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess','almacen_articulo.index');

        $categoria   = DB::table('categorias')->where('status', '=', 1)->get();
        $marca       = DB::table('marcas')->where('status', '=', 1)->get();
        $unidad      = DB::table('unidades')->where('status', '=', 1)->get();
        $variaciones = DB::table('variaciones')->where('status', '=', 1)->get();

        return view('almacen.articulo.index', [
            'categoria'   => $categoria   ?? collect(),
            'marca'       => $marca       ?? collect(),
            'unidad'      => $unidad      ?? collect(),
            'variaciones' => $variaciones ?? collect(),
        ]);
    }

    /**
     * Lista de precios para clientes: PDF con marca Sommy, foto de cada
     * producto y precio de venta por variante (o precio unico).
     */
    public function listaPreciosPdf()
    {
        $productos = Articulo::with(['categoria', 'combinaciones', 'imagenes'])
            ->where('estado', 'Activo')
            ->orderBy('categoria_id')->orderBy('nombre')
            ->get()
            ->filter(fn ($a) => $a->combinaciones->isNotEmpty() || (float) $a->pventa_con_iva > 0)
            ->map(function ($a) {
                // Foto: imagen principal de la galeria, o la imagen base del producto
                $path = optional($a->imagenes->firstWhere('principal', true) ?: $a->imagenes->first())->path;
                if (!$path && $a->imagen) {
                    $path = 'imagenes/articulos/' . $a->imagen;
                }
                $absoluta = $path ? public_path($path) : null;
                $a->foto_local = ($absoluta && file_exists($absoluta)) ? $absoluta : null;
                return $a;
            });

        $empresa = DB::table('configuracion')->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('almacen.articulo.lista_precios_pdf', [
            'productos' => $productos,
            'empresa'   => $empresa,
            'fecha'     => now()->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->stream('lista-de-precios-sommy.pdf');
    }

    public function store(Request $request)
    {
        try {
            // Producto con variantes (tipo 2): el precio vive en cada variante,
            // el precio base no se pide
            $esVariante = (string) $request->input('tipo_producto') === '2';

            $rules = array_merge([
                'nombre' => 'required',
                'idcategoria' => 'required',
                'codigo' => 'nullable|unique:productos,codigo|max:13',
                'tipo_producto' => 'required',
                'pcompra-sin-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pcompra-con-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pventa-sin-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pventa-con-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'articulo_des' => 'required|numeric|min:0|max:100',
                'descripcion' => 'required',
                'idmarca' => 'required',
                'idunidad' => 'required',
                'iva_compra_id' => 'required',
            ], $this->fichaTecnicaRules());

            $messages = [
                'nombre.required' => 'El nombre es requerido',
                'idcategoria.required' => 'La categoría es requerida',
                'codigo.unique' => 'El código ya existe',
                'tipo_producto.required' => 'El tipo de producto es requerido',
                'pcompra-sin-iva.required' => 'El precio de compra sin IVA es requerido',
                'pventa-sin-iva.required' => 'El precio de venta sin IVA es requerido',
                'descripcion.required' => 'La descripción es requerida',
                'articulo_des.required' => 'Debes poner el descuento en porcentaje (0 si no aplica)',
                'articulo_des.numeric' => 'El descuento debe ser un número',
                'articulo_des.min' => 'El descuento no puede ser menor a 0%',
                'articulo_des.max' => 'El descuento no puede ser mayor a 100%',
                'idmarca.required' => 'La marca es requerida',
                'idunidad.required' => 'La unidad es requerida',
                'iva_compra_id.required' => 'El IVA de compra es requerido',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'estado' => 'errorvalidacion',
                    'mensaje'=> $validator->errors()->all(),
                    'class_name' => 'alert-danger'
                ]);
            }

            DB::beginTransaction();

            // ✅ Imagen principal (nombre hasheado, evita colisiones)
            $imagen = $this->guardarImagenPrincipal($request) ?? 'productogeneral.png';

            // ✅ Crear artículo
            $articulo = Articulo::create(array_merge([
                'tipo_producto_id'       => $request->get('tipo_producto'),
                'categoria_id'           => $request->get('idcategoria'),
                'marca_id'               => $request->get('idmarca'),
                'unidad_id'              => $request->get('idunidad'),
                'codigo'                 => $request->get('codigo') ?? '',
                'nombre'                 => $request->get('nombre'),
                'nombre_compra'          => $request->get('nombre_compra') ?: null,
                'descripcion'            => $request->get('descripcion'),
                'estado'                 => 'Activo',
                'descuento'              => $request->get('articulo_des'),
                'iva_compra_id'          => $request->get('iva_compra_id'),
                'pcompra_sin_iva'        => $request->get('pcompra-sin-iva'),
                'pcompra_con_iva'        => $request->get('pcompra-con-iva'),
                'iva_venta_id'           => $request->get('iva_venta_id'),
                'pventa_sin_iva'         => $request->get('pventa-sin-iva'),
                'pventa_con_iva'         => $request->get('pventa-con-iva'),
                'imagen'                 => $imagen,
                'ubicacion'              => $request->get('ubicacion') ?? '',
                'articulo_pesable_balanza' => $request->has('articulo_pesable_balanza') ? 1 : 0,
            ], $this->fichaTecnicaData($request)));

            // ✅ Registrar imagen principal + galería en producto_imagenes
            $this->sincronizarImagenPrincipal($articulo);
            $this->guardarImagenesGaleria($request, $articulo->idarticulo);

            // ✅ Generar código automático si no se envió
            if ($articulo->codigo == "") {
                $articulo->update([
                    'codigo'=> str_pad($articulo->idarticulo, 13, '0', STR_PAD_LEFT)
                ]);
            }

            // ✅ GUARDAR ATRIBUTOS
            $atributos = json_decode($request->atributos, true);
            if (!empty($atributos)) {

                foreach ($atributos as $attr) {

                    // Guardar atributo
                    $atributo = ProductoAtributo::create([
                        'producto_id' => $articulo->idarticulo,
                        'nombre' => $attr['nombre']
                    ]);

                    // Guardar variantes
                    foreach ($attr['variantes'] as $var) {
                        ProductoAtributoVariante::create([
                            'atributo_id' => $atributo->id,
                            'valor' => $var
                        ]);
                    }
                }
            }

            // ✅ GUARDAR COMBINACIONES (VIENEN DEL FRONTEND)
            $combinaciones = json_decode($request->combinaciones, true);

            if (!empty($combinaciones)) {
                foreach ($combinaciones as $index => $combo) {

                    $nuevaCombinacion = ProductoCombinacion::create([
                        'producto_id'        => $articulo->idarticulo,
                        'combinacion'        => implode(' / ', $combo['combinacion']),
                        'sku'                => !empty($combo['sku']) ? trim($combo['sku']) : null,
                        'json_detalle'       => $combo['combinacion'],
                        'pcompra_variante'   => $combo['pcompra'],
                        'pventa_variante'    => $combo['pventa']
                    ]);

                    $this->guardarImagenCombinacion($request, $articulo->idarticulo, $nuevaCombinacion->idcombinacion, $index);
                }

                // SKU autogenerado para las que quedaron sin código
                $this->autogenerarSkusCombinaciones($articulo->idarticulo);
            }

            DB::commit();

            return response()->json([
                'estado'=> 1,
                'product'=>$articulo,
                'mensaje' => 'Se guardó el artículo con atributos, variantes y combinaciones'
            ]);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'estado'=> 0,
                'mensaje' => ['Excepción capturada: '.$th->getMessage()],
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('haveaccess','almacen_articulo.index');

        $categoria    = DB::table('categorias')->where('status', '=', 1)->get();
        $marca        = DB::table('marcas')->where('status', '=', 1)->get();
        $unidad       = DB::table('unidades')->where('status', '=', 1)->get();
        $variaciones  = DB::table('variaciones')->where('status', '=', 1)->get();
        $tipoProducto = DB::table('tipo_producto')->where('status', '=', 1)->get();
        $iva          = DB::table('iva')->get();
        $proveedores  = DB::table('proveedores')->where('estado', '!=', 'Inactivo')->orderBy('nombre')->get();

        /******************************* */
        $getVariacion = null;
        /******************************** */

        return view('almacen.articulo.create', [
            'categoria'    => $categoria ?? collect(),
            'marca'        => $marca ?? collect(),
            'unidad'       => $unidad ?? collect(),
            'variaciones'  => $variaciones,
            'tipoProducto' => $tipoProducto,
            'getVariacion' => $getVariacion,
            'iva'          => $iva,
            'proveedores'  => $proveedores,
            'tiposColchon' => Articulo::TIPOS_COLCHON,
            'firmezas'     => Articulo::FIRMEZAS,
            'plazasOpts'   => Articulo::PLAZAS,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // Stock total por producto: simples (sucursal_articulo) + variantes (sucursal_combinacion)
        $stockSimples = DB::table('sucursal_articulo')
            ->where('activo', 1)
            ->selectRaw('articulo_id, SUM(stock) as stock')
            ->groupBy('articulo_id');

        $stockVariantes = DB::table('producto_combinaciones as pc')
            ->join('sucursal_combinacion as scb', 'scb.combinacion_id', '=', 'pc.idcombinacion')
            ->where('scb.activo', 1)
            ->selectRaw('pc.producto_id, SUM(scb.stock) as stock')
            ->groupBy('pc.producto_id');

        $articulo = DB::table('productos as p')
            ->join('tipo_producto as tp', 'p.tipo_producto_id', '=', 'tp.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.idcategoria')
            ->leftJoin('marcas as m', 'p.marca_id', '=', 'm.idmarca')
            ->leftJoinSub($stockSimples, 'ss', 'ss.articulo_id', '=', 'p.idarticulo')
            ->leftJoinSub($stockVariantes, 'sv', 'sv.producto_id', '=', 'p.idarticulo')
            ->select(
                'p.idarticulo',
                'p.codigo',
                'p.nombre',
                'c.nombre as categoria',
                'm.nombre as marca',
                'p.pcompra_sin_iva',
                'p.pcompra_con_iva',
                'p.pventa_sin_iva',
                'p.pventa_con_iva',
                'p.tipo_producto_id',
                'tp.name as tipo_producto'
            )
            ->selectRaw('COALESCE(ss.stock, 0) + COALESCE(sv.stock, 0) as stock_total')
            ->addSelect('p.imagen')
            ->where('p.estado', '=', 'Activo')
            ->get();

        // Foto del artículo (nunca por variante: todas comparten la misma foto principal)
        $fotos = DB::table('producto_imagenes')
            ->whereNull('combinacion_id')
            ->orderByDesc('principal')
            ->orderBy('orden')
            ->get(['producto_id', 'path'])
            ->groupBy('producto_id')
            ->map(fn ($g) => $g->first()->path);

        return DataTables::of($articulo)
            ->addColumn('select', fn($a) =>
                '<input type="checkbox" class="row-select-product" data-id="'.$a->idarticulo.'" />'
            )
            ->addColumn('producto', function ($a) use ($fotos) {
                $ruta = $fotos->get($a->idarticulo) ?: ($a->imagen ? 'imagenes/articulos/'.$a->imagen : null);
                $foto = $ruta
                    ? '<img src="'.asset($ruta).'" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;flex-shrink:0;">'
                    : '<div style="width:44px;height:44px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-image text-muted"></i></div>';

                $sub = implode(' · ', array_filter([$a->codigo, $a->categoria, $a->marca]));
                $chip = $a->tipo_producto_id == 2
                    ? ' <span class="badge" style="background:#EDE9FE;color:#5B21B6;font-size:0.62rem;">Variantes</span>'
                    : '';
                return '<div style="display:flex;align-items:center;gap:10px;">'.$foto
                     . '<div><div style="font-weight:700;color:#0f172a;">'.e($a->nombre).$chip.'</div>'
                     . '<div style="font-size:0.75rem;color:#94a3b8;">'.e($sub).'</div></div></div>';
            })
            ->addColumn('stock_badge', function ($a) {
                $stock = (float) $a->stock_total;
                if ($stock <= 0) {
                    return '<span class="badge" style="background:#FEE2E2;color:#B91C1C;">Sin stock</span>';
                }
                $color = $stock <= 3 ? 'background:#FEF3C7;color:#92400E;' : 'background:#DCFCE7;color:#15803D;';
                return '<span class="badge" style="'.$color.'font-size:0.8rem;">'.rtrim(rtrim(number_format($stock, 2, ',', '.'), '0'), ',').' u.</span>';
            })
            ->addColumn('costo_fmt', fn($a) => '$'.number_format($a->pcompra_con_iva, 2, ',', '.'))
            ->addColumn('venta_fmt', function ($a) {
                // Con variantes el precio de venta es POR variante: se listan todas
                $variantes = DB::table('producto_combinaciones')
                    ->where('producto_id', $a->idarticulo)
                    ->orderBy('idcombinacion')
                    ->get(['combinacion', 'pventa_variante']);

                if ($variantes->isNotEmpty()) {
                    return $variantes->map(fn ($v) =>
                        '<div style="font-size:0.78rem;white-space:nowrap;"><span style="color:#94a3b8;">'.e($v->combinacion).':</span> '
                        . '<strong>$'.number_format((float) $v->pventa_variante, 2, ',', '.').'</strong></div>'
                    )->implode('');
                }

                return '<strong>$'.number_format($a->pventa_con_iva, 2, ',', '.').'</strong>';
            })
            ->addColumn('margen', function ($a) {
                if ($a->pcompra_sin_iva <= 0) {
                    return '<span class="text-muted">—</span>';
                }
                $pct = round((($a->pventa_sin_iva - $a->pcompra_sin_iva) / $a->pcompra_sin_iva) * 100);
                $color = $pct <= 0 ? '#B91C1C' : ($pct < 20 ? '#92400E' : '#15803D');
                return '<span style="font-weight:800;color:'.$color.';">'.($pct > 0 ? '+' : '').$pct.'%</span>'
                     . '<div style="font-size:0.72rem;color:#94a3b8;">$'.number_format($a->pventa_sin_iva - $a->pcompra_sin_iva, 0, ',', '.').' x u.</div>';
            })
            ->addColumn('action', function($a){
                $id = $a->idarticulo;
                $tieneVentas = DB::table('detalle_ventas')->where('articulo_id',$id)->exists();

                $btnEdit = '
                    <button class="btn btn-sm btn-primary" onclick="window.location.href=\'/articulo/'.$id.'/edit\'">
                        <i class="fas fa-edit"></i>
                    </button>';

                $btnConocimiento = '
                    <button class="btn btn-sm btn-outline-info" onclick="window.location.href=\'/articulo/'.$id.'/conocimiento\'" title="Conocimiento del producto (interno: bot y publicaciones)">
                        <i class="fas fa-brain"></i>
                    </button>';

                // Combinaciones con SKU para imprimir etiqueta por medida
                $dataCombinaciones = '';
                if ($a->tipo_producto_id == 2) {
                    $combs = DB::table('producto_combinaciones')
                        ->where('producto_id', $id)
                        ->orderBy('combinacion')
                        ->get(['idcombinacion', 'combinacion', 'sku', 'pventa_variante']);

                    if ($combs->isNotEmpty()) {
                        $dataCombinaciones = ' data-combinaciones="'.e($combs->toJson()).'"';
                    }
                }

                $btnBarcode = '
                    <button class="btn btn-sm btn-outline-secondary btn-print-barcode"
                        data-id="'.e($id).'"
                        data-codigo="'.e($a->codigo).'"
                        data-nombre="'.e($a->nombre).'"
                        data-precio="'.number_format($a->pventa_con_iva, 2, ',', '.').'"'.$dataCombinaciones.'
                        title="Imprimir etiqueta">
                        <i class="fas fa-barcode"></i>
                    </button>';

                if (!$tieneVentas) {
                    $btnDelete = '
                        <button class="btn btn-sm btn-danger" onclick="delete_product('.$id.')">
                            <i class="fas fa-trash-alt"></i>
                        </button>';
                } else {
                    $btnDelete = '
                        <button class="btn btn-sm btn-warning" disabled title="El artículo tiene ventas">
                            <i class="fas fa-exclamation-triangle"></i>
                        </button>';
                }

                return '
                    <div style="display:flex; gap:8px; align-items:center; justify-content:center;">
                        '.$btnEdit.$btnConocimiento.$btnBarcode.$btnDelete.'
                    </div>
                ';
            })
            ->rawColumns(['select','producto','stock_badge','venta_fmt','margen','action'])
            ->make(true);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {

            // ✅ Obtener producto
            $producto = Articulo::findOrFail($id);

            // ✅ Obtener atributos con variantes
            $atributos = ProductoAtributo::with('variantes')
                ->where('producto_id', $id)
                ->get();

            // ✅ Obtener combinaciones
            $combinaciones = ProductoCombinacion::where('producto_id', $id)->get();

            return response()->json([
                'estado' => 1,
                'producto' => $producto,
                'atributos' => $atributos,
                'combinaciones' => $combinaciones
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Error al cargar el producto: '.$th->getMessage()
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $articulo = Articulo::findOrFail($request->productoId);

            // ✅ Validaciones (con variantes el precio va por variante, no base)
            $esVariante = (string) $request->input('tipo_producto') === '2';

            $rules = array_merge([
                'nombre' => 'required',
                'idcategoria' => 'required',
                'codigo' => 'required|unique:productos,codigo,' . $request->productoId . ',idarticulo',
                'tipo_producto' => 'required',
                'pcompra-sin-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pcompra-con-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pventa-sin-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'pventa-con-iva' => $esVariante ? 'nullable|numeric' : 'required|numeric',
                'articulo_des' => 'required|numeric|min:0|max:100',
                'descripcion' => 'required',
                'idmarca' => 'required',
                'idunidad' => 'required',
                'iva_compra_id' => 'required',
                'iva_venta_id' => 'required',
            ], $this->fichaTecnicaRules());

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'estado' => 'errorvalidacion',
                    'mensaje'=> $validator->errors()->all(),
                    'class_name' => 'alert-danger'
                ]);
            }

            // ✅ Actualizar campos principales
            $articulo->tipo_producto_id = $request->tipo_producto;
            $articulo->categoria_id = $request->idcategoria;
            $articulo->marca_id = $request->idmarca;
            $articulo->unidad_id = $request->idunidad;

            $articulo->codigo = $request->codigo;
            $articulo->nombre = $request->nombre;
            $articulo->nombre_compra = $request->nombre_compra ?: null;
            $articulo->descripcion = $request->descripcion;
            $articulo->estado = "Activo";
            $articulo->descuento = $request->articulo_des;

            // ✅ Precios e IVA
            $articulo->iva_compra_id = $request->iva_compra_id;
            $articulo->pcompra_sin_iva = $request->input('pcompra-sin-iva');
            $articulo->pcompra_con_iva = $request->input('pcompra-con-iva');

            $articulo->iva_venta_id = $request->iva_venta_id;
            $articulo->pventa_sin_iva = $request->input('pventa-sin-iva');
            $articulo->pventa_con_iva = $request->input('pventa-con-iva');

            // ✅ Checkbox balanza
            $articulo->articulo_pesable_balanza = $request->has('articulo_pesable_balanza') ? 1 : 0;

            // ✅ Ficha técnica colchón + proveedor
            $articulo->fill($this->fichaTecnicaData($request));

            // ✅ Imagen principal (nombre hasheado)
            if ($nuevaImagen = $this->guardarImagenPrincipal($request)) {
                $articulo->imagen = $nuevaImagen;
            }

            // ✅ Guardar cambios del producto
            $articulo->save();

            // ✅ Imagen principal + galería en producto_imagenes
            $this->sincronizarImagenPrincipal($articulo);
            $this->guardarImagenesGaleria($request, $articulo->idarticulo);

            // ✅ Procesar atributos y combinaciones SOLO si es producto personalizado
            if ($articulo->tipo_producto_id == 2) {

                // ✅ Decodificar JSON recibido
                $atributosPayload = json_decode($request->atributos, true) ?? [];
                $combinacionesPayload = json_decode($request->combinaciones, true) ?? [];

                // Sincronizar ATRIBUTOS
                $existingAtributos = ProductoAtributo::where('producto_id', $articulo->idarticulo)->get();
                $existingAttrIds   = $existingAtributos->pluck('id')->all();
                $incomingAttrIds   = collect($atributosPayload)->pluck('id')->filter()->all();

                // Eliminar atributos que no vinieron
                $toDeleteAttr = array_diff($existingAttrIds, $incomingAttrIds);
                if (!empty($toDeleteAttr)) {
                    ProductoAtributoVariante::whereIn('atributo_id', $toDeleteAttr)->delete();
                    ProductoAtributo::whereIn('id', $toDeleteAttr)->delete();
                }

                // Crear/Actualizar atributos
                foreach ($atributosPayload as $attr) {
                    if (!empty($attr['id'])) {
                        // Update existente
                        $atributo = ProductoAtributo::where('producto_id', $articulo->idarticulo)
                            ->where('id', $attr['id'])
                            ->first();

                        if ($atributo) {
                            $atributo->update(['nombre' => $attr['nombre']]);

                            // Sincronizar variantes
                            $existingVar = ProductoAtributoVariante::where('atributo_id', $atributo->id)->pluck('valor')->all();
                            $incomingVar = $attr['variantes'] ?? [];

                            // Eliminar variantes que no vinieron
                            $toDeleteVar = array_diff($existingVar, $incomingVar);
                            if (!empty($toDeleteVar)) {
                                ProductoAtributoVariante::where('atributo_id', $atributo->id)
                                    ->whereIn('valor', $toDeleteVar)
                                    ->delete();
                            }

                            // Insertar nuevas variantes
                            $toInsertVar = array_diff($incomingVar, $existingVar);
                            foreach ($toInsertVar as $var) {
                                ProductoAtributoVariante::create([
                                    'atributo_id' => $atributo->id,
                                    'valor'       => $var
                                ]);
                            }
                        }
                    } else {
                        // Crear nuevo atributo
                        $nuevoAtributo = ProductoAtributo::create([
                            'producto_id' => $articulo->idarticulo,
                            'nombre'      => $attr['nombre']
                        ]);

                        foreach ($attr['variantes'] as $var) {
                            ProductoAtributoVariante::create([
                                'atributo_id' => $nuevoAtributo->id,
                                'valor'       => $var
                            ]);
                        }
                    }
                }

                // Sincronizar COMBINACIONES
                $existingComb = ProductoCombinacion::where('producto_id', $articulo->idarticulo)->get();
                $existingCombIds = $existingComb->pluck('idcombinacion')->all();
                $incomingCombIds = collect($combinacionesPayload)->pluck('idcombinacion')->filter()->all();

                // Eliminar combinaciones que no vinieron (y limpiar vínculos en sucursales)
                $toDeleteComb = array_diff($existingCombIds, $incomingCombIds);
                
                if (!empty($toDeleteComb)) {
                    // Borrar también la imagen propia de cada combinación eliminada
                    $imagenesComb = ProductoImagen::whereIn('combinacion_id', $toDeleteComb)->get();
                    foreach ($imagenesComb as $img) {
                        if (File::exists(public_path($img->path))) {
                            File::delete(public_path($img->path));
                        }
                        $img->delete();
                    }

                    SucursalCombinacion::whereIn('combinacion_id', $toDeleteComb)->delete();
                    ProductoCombinacion::whereIn('idcombinacion', $toDeleteComb)->delete();
                }

                // Crear/Actualizar combinaciones
                foreach ($combinacionesPayload as $index => $combo) {
                    $detalleArray = (array)($combo['combinacion'] ?? []);
                    $fields = [
                        'combinacion'       => implode(' / ', $detalleArray),
                        'sku'               => !empty($combo['sku']) ? trim($combo['sku']) : null,
                        'json_detalle'      => $detalleArray, // si tu columna no es JSON, usar json_encode($detalleArray)
                        'pcompra_variante'  => $combo['pcompra'] ?? 0,
                        'pventa_variante'   => $combo['pventa'] ?? 0,
                    ];

                    if (!empty($combo['idcombinacion'])) {
                        // Update existente
                        $model = ProductoCombinacion::where('producto_id', $articulo->idarticulo)
                            ->where('idcombinacion', $combo['idcombinacion'])
                            ->first();

                        if ($model) {
                            $model->update($fields);
                            $this->guardarImagenCombinacion($request, $articulo->idarticulo, $model->idcombinacion, $index);
                        }
                    } else {
                        // Crear nueva (no se inicializa stock en sucursales aquí)
                        $nuevaCombinacion = ProductoCombinacion::create(array_merge($fields, [
                            'producto_id' => $articulo->idarticulo,
                        ]));

                        $this->guardarImagenCombinacion($request, $articulo->idarticulo, $nuevaCombinacion->idcombinacion, $index);
                    }
                }

                // SKU autogenerado para las que quedaron sin código
                $this->autogenerarSkusCombinaciones($articulo->idarticulo);
            }


            DB::commit();

            return response()->json([
                'estado'=> 1,
                'mensaje' => 'Se actualizó correctamente el artículo'
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'estado'=> 0,
                'mensaje' => ['Ocurrió un error: '.$th->getMessage()],
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $articulo = Articulo::find($request->id);
            $articulo->estado="Inactivo";
            $articulo->update();
            return response()->json([
                "estado"=>1,
                "mensaje"=>"Se elimino correctamente el articulo"
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estado'=> 0,  
                'mensaje' => (array) $m,
            ]);
        }
        
    }

    public function get_data_excel(Request $request)
    {
        try {

            $rules = [
                'category_excel' => 'required',
                'file_excel_articulos' => 'required|mimes:xlsx',
            ];

            $messages = [
                'category_excel.required' => 'Debes seleccionar una categoria',
                'file_excel_articulos.required' => 'Debes de elegir un archivo excel',
                'file_excel_articulos.mimes' => 'No es un archivo excel',
            ];


            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'estado' => 'errorvalidacion',
                    'mensaje'=> $validator->errors()->all(),
                    'uploaded_image' => '',
                    'class_name' => 'alert-danger'
                ]);
            }

            $file = $request->file("file_excel_articulos");
            $array = Excel::toArray(new ArticulosImport, $file);
            $arraydata = [];
            $count = 0;
            for ($i=0; $i <count($array) ; $i++) { 
                for ($j=0; $j < count($array[$i]); $j++) { 
                    $count++;
                    if ($count != 1) {
                        $position_zero = $array[$i][$j][0];
                        $position_one = $array[$i][$j][1];
                        $position_two = $array[$i][$j][2];
                        $position_three = $array[$i][$j][3];
                        $position_four = $array[$i][$j][4];
                        $position_five = $array[$i][$j][5];
                        $position_six = $array[$i][$j][6];

                        $arraydata[] = [
                            "codigo"=>$position_zero,
                            "name"=>$position_one,
                            "stock"=>$position_two,
                            "pcompra"=>$position_three,
                            "pventa"=>$position_four,
                            "descripcion"=>$position_five,
                            "descuento"=>$position_six,
                        ];
                    }


                }
            }
            return response()->json([
                "estado"=>1,
                "categoria" => $request->category_excel,
                "datos"=>$arraydata
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estado'=> 0,  
                'mensaje' => (array) $m,
            ]);
        }
    }

    public function save_products(Request $request)
    {
        try {
            $categoria = $request->upload_category;
            $codigo = $request->upload_codigo;
            $name = $request->upload_name;
            $stock = $request->upload_stock;
            $pcompra = $request->upload_compra;
            $pventa = $request->upload_venta;
            $description = $request->upload_description;
            $descuento = $request->upload_descuento;

            $count = 0;
            $arraycodigo = [];
            while ($count < count($categoria)) {
                $validate_codigo = $codigo[$count];

                $searchcodigo = DB::table('productos')->where('codigo','=', $validate_codigo)->get();
                $true = count($searchcodigo);
                if ($true > 0) {
                    $arraycodigo[] = ["codigo_existente"=>$true, "num_codigo" => $validate_codigo];
                }
                $count=$count+1;
            }
            $total_registros = count($arraycodigo);
            if ($total_registros > 0) {
                return response()->json([
                    "estado" => "error_codigo",
                    "existente" => $arraycodigo,
                ]);
            }
            $row = 0;
            for ($i=0; $i < count($categoria); $i++) { 
                //echo $i;
                if ($stock[$i] != "") {
                    $articulos = new Articulo;
                    $articulos->categoria_id= $categoria[$i];             
                    $articulos->codigo = $codigo[$i];             
                    $articulos->nombre = $name[$i];             
                    $articulos->stock = $stock[$i];             
                    $articulos->pcompra = $pcompra[$i];             
                    $articulos->pventa= $pventa[$i];             
                    $articulos->descripcion= $description[$i];             
                    $articulos->imagen='productogeneral.png';
                    $articulos->estado="Activo";
                    $articulos->descuento = $descuento[$i];
                    $articulos->iva='0.00';
                    $articulos->save();
                    $row++;
                }
            }

            return response()->json([
                'estado'=> 1,  
                'mensaje' => 'Exito. Se importaron <strong>'.$row.'</strong> articulos',
                //"request" =>  $categoria,
                //"codigo" => $codigo,
                //"rows" => $row
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estado'=> 0,  
                'mensaje' => (array) $m,
            ]);
        }
    }

    public function update_stock(Request $request)
    {
        try {
            
            $id = $request->articuloId;
            $newstock = $request->newStock;

            $articulo = Articulo::find($id);
            $articulo->stock = $newstock;
            $articulo->save();

            return response()->json([
                "status" => 1,
                "message" => "Se actualizo el artículo con exito",
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => 0,
                "message" => "Ocurrio un error: ". $th->getMessage(),
            ]);
        }
    }

    public function exportArticulo()
    {
        return Excel::download(new articuloExport, 'articulos.xlsx');
    }

    public function get_variantes($id,$idproducto)
    {
        try {
            $result = DB::table('variaciones as v')
            ->join('variantes_para_variaciones as vpv', 'v.id', '=', 'vpv.variacion_id') 
            ->select('vpv.*') 
            ->where([['vpv.status','=',1],['vpv.variacion_id','=',$id]])
            ->get();

            $productIntegration = DB::table('producto_integracion_variante as pvv')
            ->join('variantes_para_variaciones as vpv', 'pvv.variante_id', '=', 'vpv.id' )
            ->select('pvv.*', 'vpv.name','vpv.id as variacionVarianteId')
            ->where([['pvv.activo','=',1], ['pvv.producto_id','=', $idproducto],['pvv.variacion_id','=',$id]])->get();
            //dd($productIntegration);
            $color = DB::table('color')->where('status','=',1)->get();

            $dataVariant = [];
            foreach ($productIntegration as $row){
                $productVarainte = DB::table('producto_integracion_variante as pvv')
                ->join('producto_variacion_variante as prv', 'pvv.id', '=', 'prv.product_integration_id' )
                ->select('prv.*')
                ->where([['prv.active','=',1], ['pvv.producto_id','=', $idproducto],['prv.product_integration_id','=', $row->id], ['pvv.variacion_id','=',$id]])
                ->get();
                $dataVariant[$row->name] = $productVarainte;
            }

            $dataExistVaraint = [];
            foreach ($productIntegration as $varianteVariacion){
                array_push($dataExistVaraint, $varianteVariacion->variacionVarianteId);
            }
            //dd($dataVariant);
            return response()->json([
                "status" => 1,
                "data" => $result,
                'color'=> $color,
                "getproductIntegration" => $productIntegration,
                'getproductVariant'=> $dataVariant,
                "message" => "Exito",
                "dataExistVaraint" => $dataExistVaraint,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 0,
                "data" => [],
                "message" => "Ocurrio un error: ". $th->getMessage(),
            ]);
        }       
    }
    
    public function edit_product($id)
    {
        $categoria = DB::table('categorias')->where('status','=','1')->get();
        $tipoProducto = DB::table('tipo_producto')->where('status','=','1')->get();
        $marca = DB::table('marcas')->where('status', '=', 1)->get();
        $unidad = DB::table('unidades')->where('status', '=', 1)->get();
        $iva = DB::table('iva')->get();

        $product = Articulo::findOrFail($id);

        // ✅ Nuevo sistema de atributos
        $atributos = ProductoAtributo::with('variantes')
            ->where('producto_id', $id)
            ->get();

        $combinaciones = ProductoCombinacion::where('producto_id', $id)->get();

        // ✅ Sistema viejo (si lo seguís usando)
        $variaciones = [];
        $getVariacion = [];

        if ($product->tipo_producto_id === 2) {
            $variaciones = DB::table('variaciones')->where('status','=','1')->get();
            $getVariacion = DB::table('producto_integracion_variante')
                ->where('producto_id', $id)
                ->where('activo', 1)
                ->first();
        }

        // ✅ Imágenes de galería (tabla producto_imagenes, sin la principal)
        $imagenesGaleria = ProductoImagen::where('producto_id', $id)
            ->where('principal', false)
            ->orderBy('orden')
            ->get();

        $proveedores = DB::table('proveedores')->where('estado', '!=', 'Inactivo')->orderBy('nombre')->get();

        return view('almacen.articulo.edit', [
            'imagenesGaleria' => $imagenesGaleria,
            'product'      => $product,
            'categoria'    => $categoria,
            'variaciones'  => $variaciones,
            'tipoProducto' => $tipoProducto,
            'getVariacion' => $getVariacion,
            'marca'        => $marca,
            'unidad'       => $unidad,
            'iva'          => $iva,
            'atributos'    => $atributos,
            'combinaciones'=> $combinaciones,
            'proveedores'  => $proveedores,
            'tiposColchon' => Articulo::TIPOS_COLCHON,
            'firmezas'     => Articulo::FIRMEZAS,
            'plazasOpts'   => Articulo::PLAZAS,
        ]);
    }
    /**
     * Show bulk upload form
     */
    public function showBulkUpload()
    {
        Gate::authorize('haveaccess','almacen_articulo.index');
        $categoria = DB::table('categorias')->where('status','=','1')->orderBy('nombre')->get();
        $tipoProducto = DB::table('tipo_producto')->where('status','=','1')->orderBy('name')->get();
        
        return view('almacen.articulo.bulk_upload', [
            'categoria' => $categoria,
            'tipoProducto' => $tipoProducto
        ]);
    }

    /**
     * Download product template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'producto_template_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Process bulk product upload
     */
    public function processBulkUpload(Request $request)
    {
        Gate::authorize('haveaccess','almacen_articulo.bulk_upload');
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new BulkProductImport();
            
            Excel::import($import, $request->file('file'));
            
            $statistics = $import->getStatistics();

            if ($statistics['success_count'] > 0) {
                $message = "Import completed successfully! {$statistics['success_count']} products imported.";
                
                if ($statistics['error_count'] > 0) {
                    $message .= " {$statistics['error_count']} rows had errors.";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'statistics' => $statistics
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No products were imported. Please check your file format and data.',
                    'statistics' => $statistics
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during import: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get categories for bulk upload
     */
    public function getCategories()
    {
        $categorias = DB::table('categorias')
            ->where('status', '1')
            ->orderBy('nombre')
            ->get(['idcategoria', 'nombre']);
            
        return response()->json($categorias);
    }

    /**
     * Get product types for bulk upload
     */
    public function getProductTypes()
    {
        $tipos = DB::table('tipo_producto')
            ->where('status', '1')
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($tipos);
    }

    /**
     * Get brands for bulk upload
     */
    public function getBrands()
    {
        $brands = DB::table('productos')
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');
            
        return response()->json($brands);
    }

    /**
     * Get locations for bulk upload
     */
    public function getLocations()
    {
        $locations = DB::table('productos')
            ->whereNotNull('ubicacion')
            ->where('ubicacion', '!=', '')
            ->distinct()
            ->orderBy('ubicacion')
            ->pluck('ubicacion');
            
        return response()->json($locations);
    }

    /**
     * Quick create category from product form
     */
    public function quickCreateCategory(Request $request)
    {
        Gate::authorize('haveaccess','almacen_articulo.quick_create');
        
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:500',
        ]);

        try {
            $category = DB::table('categorias')->insertGetId([
                'nombre' => trim($request->nombre),
                'descripcion' => trim($request->descripcion ?? ''),
                'status' => 1,
                'name_imagen' => 'default.jpg',
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ]);

            $newCategory = DB::table('categorias')
                ->where('idcategoria', $category)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'category' => $newCategory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Quick create marca from product form
     */
    public function quickCreateMarca(Request $request)
    {
        Gate::authorize('haveaccess','almacen_articulo.quick_create');

        $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            $marcaId = DB::table('marcas')->insertGetId([
                'nombre' => trim($request->nombre),
                'descripcion' => trim($request->descripcion ?? ''),
                'status' => 1,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ]);

            $newMarca = DB::table('marcas')
                ->where('idmarca', $marcaId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Marca creada exitosamente',
                'marca' => $newMarca
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la marca: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Quick create unidad from product form
     */
    public function quickCreateUnidad(Request $request)
    {
        Gate::authorize('haveaccess','almacen_articulo.quick_create');

        $request->validate([
            'nombre' => 'required|string|max:100|unique:unidades,nombre',
            'nombre_corto' => 'required|string|max:10|unique:unidades,nombre_corto',
            'decimal' => 'boolean',
        ]);

        try {
            $unidadId = DB::table('unidades')->insertGetId([
                'nombre' => trim($request->nombre),
                'nombre_corto' => trim($request->nombre_corto),
                'decimal' => $request->decimal ?? 0,
                'status' => 1,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ]);

            $newUnidad = DB::table('unidades')
                ->where('idunidad', $unidadId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Unidad creada exitosamente',
                'unidad' => $newUnidad
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la unidad: ' . $e->getMessage()
            ]);
        }
    }

    public function listar()
    {
        $articulos = Articulo::where('estado', 'Activo')
            ->with(['marca:idmarca,nombre', 'categoria:idcategoria,nombre'])
            ->orderBy('nombre', 'asc')
            ->get(['idarticulo', 'nombre', 'codigo', 'marca_id', 'tipo_producto_id', 'categoria_id']);

        $resultado = [];

        foreach ($articulos as $articulo) {
            if ($articulo->tipo_producto_id == 1) {
                $resultado[] = [
                    'idarticulo' => $articulo->idarticulo,
                    'nombre'     => $articulo->nombre,
                    'codigo'     => $articulo->codigo,
                    'marca'      => $articulo->marca?->nombre,
                    'categoria'  => $articulo->categoria?->nombre,
                    'tipo'       => 'simple'
                ];
            } else {
                $resultado[] = [
                    'idarticulo' => $articulo->idarticulo,
                    'nombre'     => $articulo->nombre,
                    'codigo'     => $articulo->codigo,
                    'marca'      => $articulo->marca?->nombre,
                    'categoria'  => $articulo->categoria?->nombre,
                    'tipo'       => 'personalizado'
                ];

                $combinaciones = ProductoCombinacion::where('producto_id', $articulo->idarticulo)
                    ->orderBy('combinacion', 'asc')
                    ->get();

                foreach ($combinaciones as $comb) {
                    $resultado[] = [
                        'idcombinacion'    => $comb->idcombinacion,
                        'producto_id'      => $articulo->idarticulo,
                        'nombre'           => $articulo->nombre,
                        'codigo'           => $articulo->codigo,
                        'sku'              => $comb->sku,
                        'marca'            => $articulo->marca?->nombre,
                        'categoria'        => $articulo->categoria?->nombre,
                        'tipo'             => 'combinacion',
                        'atributos'        => $comb->combinacion,
                        'detalle'          => $comb->json_detalle,
                        'pcompra_variante' => $comb->pcompra_variante,
                        'pventa_variante'  => $comb->pventa_variante
                    ];
                }
            }
        }

        return response()->json([
            'estado' => 1,
            'items'  => $resultado
        ]);
    }

    public function loadOptionsCategoria()
    {
        return \App\Models\Categoria::select('idcategoria as id','nombre')
            ->orderBy('nombre')
            ->get();
    }

    public function loadOptionsMarca()
    {
        return \App\Models\Marca::select('idmarca as id','nombre')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Reglas de validación de la ficha técnica de colchón (todos los campos opcionales).
     */
    private function fichaTecnicaRules(): array
    {
        return [
            'tipo_colchon'      => 'nullable|in:' . implode(',', array_keys(Articulo::TIPOS_COLCHON)),
            'firmeza'           => 'nullable|in:' . implode(',', array_keys(Articulo::FIRMEZAS)),
            'plazas'            => 'nullable|in:' . implode(',', array_keys(Articulo::PLAZAS)),
            'altura_cm'         => 'nullable|numeric|min:0|max:999',
            'densidad_kg_m3'    => 'nullable|numeric|min:0|max:9999',
            'peso_max_kg'       => 'nullable|integer|min:0|max:65535',
            'garantia_anios'    => 'nullable|integer|min:0|max:50',
            'noches_prueba'     => 'nullable|integer|min:0|max:365',
            'certificaciones'   => 'nullable|string|max:500',
            'tela'              => 'nullable|string|max:100',
            'proveedor_id'      => 'nullable|exists:proveedores,idproveedor',
            'codigo_proveedor'  => 'nullable|string|max:50',
        ];
    }

    /**
     * Datos de ficha técnica + proveedor a persistir desde el request.
     */
    private function fichaTecnicaData(Request $request): array
    {
        $val = fn($campo) => $request->filled($campo) ? $request->input($campo) : null;

        return [
            'tipo_colchon'     => $val('tipo_colchon'),
            'firmeza'          => $val('firmeza'),
            'plazas'           => $val('plazas'),
            'altura_cm'        => $val('altura_cm'),
            'densidad_kg_m3'   => $val('densidad_kg_m3'),
            'peso_max_kg'      => $val('peso_max_kg'),
            'garantia_anios'   => $val('garantia_anios'),
            'noches_prueba'    => $val('noches_prueba'),
            'certificaciones'  => $val('certificaciones'),
            'tela'             => $val('tela'),
            // Checkbox: si el producto es colchón, sin tildar = "no tiene"; si no, queda sin dato
            'pillow_top'       => $request->has('pillow_top') ? 1 : ($request->filled('tipo_colchon') ? 0 : null),
            'proveedor_id'     => $val('proveedor_id'),
            'codigo_proveedor' => $val('codigo_proveedor'),
        ];
    }

    /**
     * Guarda la imagen principal con nombre hasheado (evita colisiones por nombre original).
     * Devuelve el nombre de archivo o null si no vino imagen.
     */
    private function guardarImagenPrincipal(Request $request): ?string
    {
        $file = $request->file('imagen');
        if (!$file || !$file->isValid()) {
            return null;
        }

        $nombre = hash('crc32b', uniqid('', true)) . time() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(public_path('/imagenes/articulos'), $nombre);

        return $nombre;
    }

    /**
     * Mantiene la fila "principal" de producto_imagenes en sincronía con productos.imagen.
     */
    private function sincronizarImagenPrincipal(Articulo $articulo): void
    {
        if (empty($articulo->imagen) || $articulo->imagen === 'productogeneral.png') {
            return;
        }

        ProductoImagen::updateOrCreate(
            ['producto_id' => $articulo->idarticulo, 'principal' => true],
            ['path' => 'imagenes/articulos/' . $articulo->imagen, 'orden' => 0]
        );
    }

    /**
     * Alta/baja/reorden de imágenes de galería (tabla producto_imagenes + archivos físicos).
     * Acepta files en imageInput[] (alta) o updateImage[] (edición) indistintamente.
     */
    private function guardarImagenesGaleria(Request $request, int $productoId): void
    {
        // Eliminar (ids de producto_imagenes, CSV o array)
        $eliminar = $request->input('imagenes_eliminar');
        if (!empty($eliminar)) {
            $ids = is_array($eliminar) ? $eliminar : explode(',', $eliminar);
            $imagenes = ProductoImagen::where('producto_id', $productoId)
                ->whereIn('id', array_filter($ids))
                ->where('principal', false)
                ->get();

            foreach ($imagenes as $img) {
                if (File::exists(public_path($img->path))) {
                    File::delete(public_path($img->path));
                }
                $img->delete();
            }
        }

        // Subir nuevas
        $files = $request->file('imageInput') ?? $request->file('updateImage') ?? [];
        if (!empty($files)) {
            $dir = public_path('imagenes/images_ecommerce/articulo-' . $productoId);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $orden = (int) ProductoImagen::where('producto_id', $productoId)->max('orden');

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }
                $nombre = hash('crc32b', uniqid('', true)) . time() . '.' . strtolower($file->getClientOriginalExtension());
                $file->move($dir, $nombre);

                ProductoImagen::create([
                    'producto_id' => $productoId,
                    'path'        => 'imagenes/images_ecommerce/articulo-' . $productoId . '/' . $nombre,
                    'orden'       => ++$orden,
                    'principal'   => false,
                ]);
            }
        }

        // Reordenar (JSON u array {id: orden})
        $ordenInput = $request->input('imagenes_orden');
        if (!empty($ordenInput)) {
            $mapa = is_array($ordenInput) ? $ordenInput : json_decode($ordenInput, true);
            if (is_array($mapa)) {
                foreach ($mapa as $id => $ord) {
                    ProductoImagen::where('producto_id', $productoId)
                        ->where('id', (int) $id)
                        ->where('principal', false)
                        ->update(['orden' => (int) $ord]);
                }
            }
        }
    }

    /**
     * Guarda (o reemplaza) la imagen propia de una combinación.
     * El front adjunta el archivo como imagen_variante_{index}, donde index es la
     * posición de la fila en la tabla de combinaciones (mismo orden que el JSON).
     */
    private function guardarImagenCombinacion(Request $request, int $productoId, int $combinacionId, int $index): void
    {
        $file = $request->file("imagen_variante_{$index}");
        if (!$file || !$file->isValid()) {
            return;
        }

        $dir = public_path('imagenes/images_ecommerce/articulo-' . $productoId);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $nombre = hash('crc32b', uniqid('', true)) . time() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $nombre);
        $path = 'imagenes/images_ecommerce/articulo-' . $productoId . '/' . $nombre;

        $existente = ProductoImagen::where('combinacion_id', $combinacionId)->first();

        if ($existente) {
            if (File::exists(public_path($existente->path))) {
                File::delete(public_path($existente->path));
            }
            $existente->update(['path' => $path]);
        } else {
            $orden = (int) ProductoImagen::where('producto_id', $productoId)->max('orden');

            ProductoImagen::create([
                'producto_id'    => $productoId,
                'combinacion_id' => $combinacionId,
                'path'           => $path,
                'orden'          => $orden + 1,
                'principal'      => false,
            ]);
        }
    }

    /**
     * Autogenera SKU 'V' + 12 dígitos para las combinaciones del producto que quedaron sin SKU.
     */
    private function autogenerarSkusCombinaciones(int $productoId): void
    {
        ProductoCombinacion::where('producto_id', $productoId)
            ->whereNull('sku')
            ->get()
            ->each(function ($comb) {
                $comb->update(['sku' => 'V' . str_pad($comb->idcombinacion, 12, '0', STR_PAD_LEFT)]);
            });
    }

}
