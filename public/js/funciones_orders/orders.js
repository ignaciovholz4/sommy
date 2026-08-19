const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let estadoActivoOrders = '';

$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': token
        }
    });
    $(function() {
    $('#orders_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showorders',
            type: 'GET',
            data: function (d) { d.status_id = estadoActivoOrders; }
           },
           columns: [
                   { data: 'order_id', name: 'order_id'},
                   { data: 'canal', name: 'canal'},
                   { data: 'cliente', name: 'cliente'},
                   { data: 'direccion', name: 'direccion', orderable: false },
                   { data: 'productos', name: 'productos', orderable: false },
                   { data: 'order_date', name: 'order_date' },
                   { data: 'total_amount', name: 'total_amount' },
                   { data: 'statusName', name: 'statusName'},
                   {data: 'action', name:'action', orderable: false, searchable: false}
                 ],
          order: [[0, 'desc']]
        });
    });

    // Pestañas del pipeline de estados: filtran el listado
    document.querySelectorAll('.estado-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.estado-tab').forEach(t => t.classList.remove('activo'));
            this.classList.add('activo');
            estadoActivoOrders = this.dataset.status;
            $('#orders_table').DataTable().ajax.reload();
        });
    });
});