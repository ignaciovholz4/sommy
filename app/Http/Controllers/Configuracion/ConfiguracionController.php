<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Gate;

use Validator;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess','configuracion.index');
        $empresa = DB::table('configuracion')->first();
        $id = $empresa->id;
        $name = $empresa->name;
        $subdomain = $empresa->subdomain;
        $image = $empresa->image;
        $adress = $empresa->adress;
        $email = $empresa->email;
        $phone = $empresa->phone;
        $array = [
            'num' => $id,
            'name'=>$name,
            'subdomain'=>$subdomain,
            'image'=> "imagenes/empresa/".$image,
            'adress'=>$adress,
            'email'=>$email,
            'phone'=>$phone,
            'cuit' => $empresa->cuit ?? '',
            'razon_social' => $empresa->razon_social ?? '',
            'cbu' => $empresa->cbu ?? '',
            'alias_cbu' => $empresa->alias_cbu ?? '',
            'descuento_transferencia' => $empresa->descuento_transferencia ?? 0,
            'whatsapp' => $empresa->whatsapp ?? '',
        ];
        //dd($empresa);
        return view('admin.configuracion.index',["empresa"=>$array]);
    }

    public function update(Request $request)
    {
        $conf = Configuracion::findOrFail($request->identificador); 

        $validationRules = [
            'name'=>'required',
            'subdomain'=>'nullable|unique:configuracion,subdomain,' . $request->identificador . '|regex:/^[a-z0-9-]+$/i',
            'adress'=>'required',
            'email'=>'required|email',
            'phone'=>'required|digits:10|numeric',
            'cuit'=>'nullable|string|max:13',
            'razon_social'=>'nullable|string|max:200',
            'cbu'=>'nullable|digits:22',
            'alias_cbu'=>'nullable|string|max:50',
            'descuento_transferencia'=>'nullable|numeric|min:0|max:100',
            'whatsapp'=>'nullable|string|max:20',
        ];

        if ($request->file('file') != null) {
            $validationRules['file'] = 'required|mimes:png,jpg,jpeg|max:2048';
        }

        $request->validate($validationRules, [
            'name.required'=>'Es necesario escribir el nombre de la empresa',
            'subdomain.unique'=>'El subdominio ya está en uso',
            'subdomain.regex'=>'El subdominio solo puede contener letras, números y guiones',
            'file.required'=>'La imagen del logo es requerido',
            'file.mimes'=>'Debe de ser una imagen png,jpg o jpeg',
            'adress.required'=>'Es necesario escribir la direccion de la empresa',
            'email.required'=>'El correo electronico es requerido',
            'email.email'=>'El formato de su correo electronico es invalido',
            'phone.required'=>'Es necesario escribir un telefono de contacto',
            'phone.digits'=>'El minimo de digitos es 10',
            'phone.numeric'=>'El numero de telefono debe ser numerico',
        ]);

        $conf->name = $request->name;
        $conf->subdomain = $request->subdomain;

        if ($file = $request->file('file')) {
            $destinationPath = public_path('/imagenes/empresa');
            $empresaImagen = trim($file->getClientOriginalName());
            $file->move($destinationPath, $empresaImagen);

            $conf->image = $empresaImagen;
        }
        $conf->adress = $request->adress;
        $conf->email = $request->email;
        $conf->phone = $request->phone;
        $conf->cuit = $request->cuit;
        $conf->razon_social = $request->razon_social;
        $conf->cbu = $request->cbu;
        $conf->alias_cbu = $request->alias_cbu;
        $conf->descuento_transferencia = $request->descuento_transferencia ?? 0;
        $conf->whatsapp = $request->whatsapp;
        $conf->update();

        return redirect()->route('config')->with('status_success','Los datos generales de la empresa se guardaron con exito');
    }
}
