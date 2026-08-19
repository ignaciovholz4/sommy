$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    const table = $('#producto_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showproducto',
            type: 'GET',
           },
           columns: [
              { data: 'select', orderable: false, searchable: false },
              { data: 'producto', name: 'nombre' },
              { data: 'stock_badge', name: 'stock_total', searchable: false },
              { data: 'costo_fmt', name: 'pcompra_con_iva', searchable: false },
              { data: 'venta_fmt', name: 'pventa_con_iva', searchable: false },
              { data: 'margen', orderable: false, searchable: false },
              { data: 'action', orderable: false, searchable: false }
          ],
          order: [[1, 'asc']]
        });

    // Select all
    $(document).on('change', '#selectAllProducts', function(){
      const checked = $(this).prop('checked');
      $(document).find('.row-select-product').prop('checked', checked);
    });

    // Open modal
    $('#bulkAddToPriceListBtn').on('click', function(){
      // gather selected ids
      const ids = $('.row-select-product:checked').map(function(){ return $(this).data('id'); }).get();
      if(ids.length === 0){
        toastr.warning('Seleccione al menos un producto');
        return;
      }
      // load lists
      $.get('/price-lists/options', function(data){
        const sel = $('#priceListSelect');
        sel.empty();
        (data || []).forEach(function(item){ sel.append(`<option value="${item.id}">${item.name} - ${item.value_type} ${item.percentage}%</option>`); });
        $('#bulkPriceListModal').modal('show');
      });
    });

    // Confirm bulk attach
    $('#confirmBulkAttach').on('click', function(){
      const listId = $('#priceListSelect').val();
      const ids = $('.row-select-product:checked').map(function(){ return $(this).data('id'); }).get();
      if(!listId){ toastr.warning('Seleccione una lista'); return; }
      $.ajax({
        url: '/price-lists/bulk-attach',
        method: 'POST',
        data: { price_list_id: listId, product_ids: ids },
        success: function(res){
          toastr.success(res.message || 'Productos agregados');
          $('#bulkPriceListModal').modal('hide');
          $('#selectAllProducts').prop('checked', false);
          table.ajax.reload(null, false);
        },
        error: function(xhr){
          toastr.error('Ocurrió un error');
        }
      });
    });
    });
});

const delete_product = (id) => {
  Swal.fire({
    title: 'Estas seguro de dar de baja el articulo?',
    text: "El articulo ya no estara disponible!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.value) {
      let url = "/delete-product";
      let formdownproduct = new FormData();
      formdownproduct.append('id', id);
      const downproduct = new acciones_articulo();
      downproduct.down_product(url,formdownproduct);
    }

  });
}

class articulo {
  constructor() {}

  msg_error(msg,errorform){
    console.log(msg)
    errorform.innerHTML = "";
    errorform.style.display = "block";
    msg.forEach(element =>{
        errorform.innerHTML += `<li>${element}</li>`;
    });

    setTimeout(() => {
        errorform.style.display = "none";
    }, 3000);
  }
  success_message(msg){
    Swal.fire(
      'Exito',
      msg,
      'success'
    )
  }
  img_preview(inputfile,container,imgpreview){
    inputfile.addEventListener("change", (e) => {
      e.preventDefault();
      const file = inputfile.files[0];
      if (file) {
        const reader = new FileReader();
        reader.addEventListener("load", (e) => {
          e.preventDefault();
          console.log(reader.result);
          imgpreview.setAttribute("src", reader.result);
        });
        reader.readAsDataURL(file);
      } else {
        imgpreview.setAttribute("src", "//placehold.it/100?text=IMAGEN");
      }
    });
  }
  refresh_table_product(){
    var TableRefreshProduct = $('#producto_table').dataTable(); 
    TableRefreshProduct.fnDraw(false);
  }
  startLoadIcon(button){
      button.innerHTML = "";
      button.disabled = true;
      button.innerHTML += `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando datos...`;
  }

  endLoadIcon(endbutton,message_end){
      endbutton.disabled = false;
      endbutton.innerHTML = message_end;
        
  }

}

class acciones_articulo extends articulo {
  down_product = async (url,form) => {
    try {
      let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      let response = await fetch(url, {
      headers: {
          'X-CSRF-TOKEN': token
      },
      method: 'POST',
      body: form
      });
      let data = await response.json();
      console.log(data);
      switch (data.estado) {
        case 1:
          Swal.fire(
            'Exito!',
            data.mensaje,
            'success'
          )
          super.refresh_table_product();
          break;
        case 0:
          alert(data.mensaje);
          break;
        default:
          break;
      }
    } catch (error) {
      console.log(error);
    }
  }
}

/* ============================================================
   MÓDULO: IMPRESIÓN DE ETIQUETAS CON CÓDIGO DE BARRAS
   Requiere: JsBarcode (cargado en el layout/index)
   ============================================================ */

const EtiquetaPrinter = (() => {

    // Datos del producto activo
    let _codigo  = '';
    let _nombre  = '';
    let _precio  = '';

    // Datos base del producto (sin variante) y combinaciones disponibles
    let _baseCodigo = '';
    let _baseNombre = '';
    let _basePrecio = '';
    let _combinaciones = [];

    // Mapa de tamaños en mm [ancho, alto]
    const TAMANOS = {
        '58x40':  [58,  40],
        '50x30':  [50,  30],
        '100x50': [100, 50],
        '100x70': [100, 70],
        '148x100':[148, 100],
    };

    function _getDimensions() {
        const sel = document.getElementById('etiqueta-tamano').value;
        if (sel === 'custom') {
            return [
                parseInt(document.getElementById('etiqueta-ancho-custom').value) || 80,
                parseInt(document.getElementById('etiqueta-alto-custom').value)  || 50,
            ];
        }
        return TAMANOS[sel] || [100, 50];
    }

    function _renderPreview() {
        const mostrarNombre = document.getElementById('etiqueta-mostrar-nombre').checked;
        const mostrarCodTxt = document.getElementById('etiqueta-mostrar-codigo-texto').checked;
        const tipoCodigo    = document.getElementById('etiqueta-tipo-codigo').value;

        // Nombre
        const elNombre = document.getElementById('preview-nombre');
        elNombre.textContent = mostrarNombre ? _nombre : '';
        elNombre.style.display = mostrarNombre ? 'block' : 'none';

        // Barcode
        const svgEl = document.getElementById('preview-barcode');
        try {
            JsBarcode(svgEl, _codigo, {
                format:      tipoCodigo,
                displayValue: mostrarCodTxt,
                fontSize:    10,
                height:      40,
                width:       1.4,
                margin:      4,
                background:  'white',
                lineColor:   '#000000',
            });
        } catch(e) {
            svgEl.innerHTML = '';
            const msg = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            msg.setAttribute('x','5'); msg.setAttribute('y','20');
            msg.setAttribute('fill','#ef4444'); msg.setAttribute('font-size','10');
            msg.textContent = 'Código no válido para ' + tipoCodigo;
            svgEl.appendChild(msg);
        }
    }

    function _buildLabelHtml(ancho, alto) {
        const mostrarNombre = document.getElementById('etiqueta-mostrar-nombre').checked;
        const mostrarCodTxt = document.getElementById('etiqueta-mostrar-codigo-texto').checked;
        const tipoCodigo    = document.getElementById('etiqueta-tipo-codigo').value;
        const cantidad      = parseInt(document.getElementById('etiqueta-cantidad').value) || 1;

        // Generar SVG del barcode como string
        const svgNS = 'http://www.w3.org/2000/svg';
        const svgEl = document.createElementNS(svgNS, 'svg');
        let barcodeHtml = '';
        try {
            JsBarcode(svgEl, _codigo, {
                format:      tipoCodigo,
                displayValue: mostrarCodTxt,
                fontSize:    Math.max(7, Math.round(alto * 0.14)),
                height:      Math.round(alto * 0.45),
                width:       Math.max(1, Math.round(ancho * 0.018)),
                margin:      3,
                background:  'white',
                lineColor:   '#000000',
            });
            const serializer = new XMLSerializer();
            barcodeHtml = serializer.serializeToString(svgEl);
        } catch(e) {
            barcodeHtml = '<p style="color:red;font-size:9px;">Código no compatible con ' + tipoCodigo + '</p>';
        }

        // Calcular fuentes y logo en proporción al tamaño
        const fzNombre = Math.max(6, Math.round(alto * 0.12)) + 'px';
        const altoLogo = Math.max(4, Math.round(alto * 0.16)) + 'mm';
        const logoUrl  = window.location.origin + '/imagenes/marca/sommy-logo-header.png';

        const labelStyle = `
            width:${ancho}mm;
            height:${alto}mm;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            padding:2mm 3mm;
            box-sizing:border-box;
            overflow:hidden;
            page-break-after:always;
            page-break-inside:avoid;
            background:white;
        `;

        let etiquetas = '';
        for (let i = 0; i < cantidad; i++) {
            etiquetas += `
                <div class="etiqueta" style="${labelStyle}">
                    <img src="${logoUrl}" alt="Sommy" style="height:${altoLogo}; width:auto; max-width:70%; object-fit:contain; margin-bottom:1mm; filter:grayscale(1) brightness(0); -webkit-filter:grayscale(1) brightness(0);">
                    ${mostrarNombre ? `<div style="font-size:${fzNombre}; font-weight:700; text-align:center; line-height:1.2; word-break:break-word; max-width:100%; margin-bottom:1mm;">${_nombre}</div>` : ''}
                    <div style="max-width:100%;">${barcodeHtml}</div>
                </div>
            `;
        }

        return `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Etiquetas - ${_nombre}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: Arial, sans-serif;
    background: white;
  }
  @page {
    size: ${ancho}mm ${alto}mm;
    margin: 0;
  }
  .etiqueta svg {
    max-width: 100%;
    height: auto;
  }
  .etiqueta img {
    filter: grayscale(1) brightness(0);
    -webkit-filter: grayscale(1) brightness(0);
  }
  @media print {
    body { margin:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .etiqueta { page-break-after: always; page-break-inside: avoid; }
    .etiqueta:last-child { page-break-after: auto; }
  }
</style>
</head>
<body>
${etiquetas}
</body>
</html>`;
    }

    function _print() {
        const [ancho, alto] = _getDimensions();
        const html = _buildLabelHtml(ancho, alto);
        const win = window.open('', '_blank', 'width=600,height=500');
        if (!win) {
            toastr.error('El navegador bloqueó la ventana de impresión. Permití las ventanas emergentes para este sitio.');
            return;
        }
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); }, 600);
    }

    function _aplicarSeleccionMedida() {
        const select = document.getElementById('etiqueta-medida');
        const valor = select ? select.value : '';

        if (valor === '' || !_combinaciones.length) {
            // Producto base
            _codigo = _baseCodigo;
            _nombre = _baseNombre;
            _precio = _basePrecio;
        } else {
            const comb = _combinaciones.find(c => String(c.idcombinacion) === valor);
            if (comb) {
                _codigo = comb.sku || _baseCodigo;
                _nombre = _baseNombre + ' — ' + comb.combinacion;
                _precio = comb.pventa_variante != null
                    ? Number(comb.pventa_variante).toLocaleString('es-AR', { minimumFractionDigits: 2 })
                    : _basePrecio;
            }
        }

        document.getElementById('etiqueta-nombre').textContent          = _nombre;
        document.getElementById('etiqueta-codigo-display').textContent  = 'Código: ' + _codigo;
    }

    function init() {
        // Abrir modal al clickear el botón de barcode
        $(document).on('click', '.btn-print-barcode', function () {
            _baseCodigo = $(this).data('codigo') + '';
            _baseNombre = $(this).data('nombre') + '';
            _basePrecio = $(this).data('precio') + '';
            _combinaciones = $(this).data('combinaciones') || [];

            // Poblar selector de medidas si el producto tiene combinaciones
            const container = document.getElementById('etiqueta-medida-container');
            const select = document.getElementById('etiqueta-medida');
            if (container && select) {
                if (Array.isArray(_combinaciones) && _combinaciones.length) {
                    select.innerHTML = '<option value="">Producto (código base)</option>' + _combinaciones.map(c =>
                        `<option value="${c.idcombinacion}">${c.combinacion} — ${c.sku || 'sin SKU'}</option>`
                    ).join('');
                    container.style.display = 'block';
                } else {
                    select.innerHTML = '';
                    container.style.display = 'none';
                    _combinaciones = [];
                }
            }

            _aplicarSeleccionMedida();
            _renderPreview();
            $('#modalImprimirEtiqueta').modal('show');
        });

        // Cambio de medida → actualizar datos y preview
        $(document).on('change', '#etiqueta-medida', function () {
            _aplicarSeleccionMedida();
            _renderPreview();
        });

        // Mostrar/ocultar campo custom size
        $(document).on('change', '#etiqueta-tamano', function () {
            const container = document.getElementById('etiqueta-custom-size');
            if (this.value === 'custom') {
                container.style.removeProperty('display');
                container.style.display = 'flex';
            } else {
                container.style.display = 'none';
            }
            _renderPreview();
        });

        // Re-renderizar preview en cualquier cambio de opción
        $(document).on('change', '#etiqueta-tipo-codigo, #etiqueta-mostrar-nombre, #etiqueta-mostrar-codigo-texto, #etiqueta-ancho-custom, #etiqueta-alto-custom', function () {
            _renderPreview();
        });

        // Botón imprimir
        $(document).on('click', '#btn-imprimir-etiqueta', function () {
            _print();
        });
    }

    return { init };

})();

// Inicializar cuando el DOM esté listo
$(document).ready(function () {
    EtiquetaPrinter.init();
});