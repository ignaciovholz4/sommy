
$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    $('#entradas_tabla').DataTable({
            "paging": true,
            "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showlistentradas',
            type: 'GET',
           },
           columns: [
                   { data: 'idingreso', name: 'idingreso'},
                   { data:'fecha_hora', name: 'fecha_hora'},
                   { data:'nombre', name: 'nombre'},
                   { data: 'folio_comprobante', name: 'folio_comprobante' },
                   { data: 'total_ingreso', name: 'total_ingreso' },
                   { data: 'estado', name: 'estado' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});

class Entradas{
    constructor(ruta,id){
        this.ruta =  ruta;
        this.id = id;
    }
    
}

class PropertiesEntradas extends Entradas{
    showdata = async () =>{
        try {
            let response = await fetch(this.ruta+this.id); 
            let json = await response.json();
            console.log(json);
            this.Listproducts(json);
        } catch (error) {
            console.log("Ocurrio un error", error);
        }
    }
    Listproducts = (json) =>{
        let prod = json.productos;
        document.querySelector("#proveedor_entrada").textContent = json.datos.nombre;
        document.querySelector("#fecha_entrada").textContent = json.datos.fecha_hora;
        document.querySelector("#folio_entrada").textContent = json.datos.folio_comprobante;
        document.querySelector("#total_entrada").value = json.datos.total_ingreso;
        const idbody = document.querySelector("#show_details_entradas");
        idbody.innerHTML = "";
        const templateprod = document.querySelector("#template-prod").content;
        //console.log(templateprod);
        const fragmentprod = document.createDocumentFragment();
        let i = 0;
        for(let item of prod){
            i++;
            console.log(item);
            templateprod.querySelector(".prod-consecutivo").textContent = i;
            templateprod.querySelector(".prod-cantidad input").value = item.cantidad;
            templateprod.querySelector(".prod-nombre").textContent = item.nombre;
            if(item.tipo_producto_id == 2) {
                templateprod.querySelector(".prod-variante").textContent = item.dataVariante.name_variante;
                templateprod.querySelector(".prod-variacion").textContent = item.dataVariante.name_color;
            }else{
                templateprod.querySelector(".prod-variante").textContent = 'No existe';
                templateprod.querySelector(".prod-variacion").textContent = 'No existe';
            }
            templateprod.querySelector(".prod-precio_compra input").value = item.precio_compra;
            templateprod.querySelector(".prod-subtotal input").value = item.subtotal;
            const clonenombre = templateprod.cloneNode(true);
            fragmentprod.appendChild(clonenombre);
        }
        idbody.appendChild(fragmentprod);
    }

    ShowModal(myModal){

        const idmodaldetalleventa = document.querySelector(myModal);
        const myModaldetalleventa = new bootstrap.Modal(idmodaldetalleventa);
        myModaldetalleventa.show();
    }
}

/**SHOW DATA WITH MODAL FROM TICKETS OF PRODUCTS*/
const show_product_entrada = (id) =>{
    console.log(id)
    let getdataproducts = new PropertiesEntradas("/get-entrada/",id);
    getdataproducts.showdata();
    ////////////////////////
    let showmodalp = new PropertiesEntradas();
    showmodalp.ShowModal("#Modalentradasproductos");
    /////////////////////////


}; 



const hide = () =>{

}

