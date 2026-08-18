
let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const customer = document.querySelector('#customer');
const numOrder = document.querySelector('#num_order');
const detailOrder = document.querySelector('#show_details_order');
const showTotalOrder = document.querySelector('#show_total_order');
$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    $('#ventas_order_ecommerce_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showventasecommerce',
            type: 'GET',
           },
           columns: [
                   { data: 'order_id', name: 'order_id'},
                   { data: 'nombre', name: 'nombre' },
                   { data: 'telefono', name: 'telefono' },
                   { data: 'status_payment', name: 'status_payment' },
                   { data: 'total', name: 'total' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});

const show_detail =  async (id) => {
    try {
        let response = await fetch(`/saleEcommerce/${id}`);
        let resp = await response.json();
        console.log(resp);
        if(resp.status === 1){
            if (customer) customer.textContent = resp.order.nombre;
            if (numOrder) numOrder.textContent = resp.order.order_id;
            if (detailOrder) detailOrder.innerHTML = "";
            resp.order_detail.forEach(detail => {
                console.log(detail);
                detailOrder.innerHTML += 
                `   <tr>
                    <td scope="col">${detail.nombre}</td>
                    <td scope="col">${detail.quantity}</td>
                    <td scope="col">${detail.price}</td>
                    <td scope="col">${detail.total}</td>
                    </tr>
                `;
            });
        }
        if (showTotalOrder) showTotalOrder.textContent = resp.order.total_amount;
        console.log(id);
        const idmodalVentaEcommerce = document.querySelector("#ModalVentasEcommerce");
        if (idmodalVentaEcommerce) {
            const myModal = new bootstrap.Modal(idmodalVentaEcommerce);
            myModal.show();
        }
    } catch (error) {
        alert("Error: " + error);
    }
}