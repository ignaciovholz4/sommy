console.log("edir preices")
const mySelect = document.getElementById('select-category');
const listProducts = document.querySelector("#show-list-products");

	
//new DataTable('#product_edit_price_table');
let table = $('#product_edit_price_table').DataTable({
  scrollCollapse: true,
    scrollY: '200px',
    paging: false,
    columnDefs: [
      {
        targets: 0,
        orderable: false,
        className: 'select-checkbox',
        render: function(data, type, full, meta){
          return '<input type="checkbox">';
        }
      }
    ],
    select: {
      style: 'multi',
      selector: 'td:first-child input[type="checkbox"]'
    },
});
// Handle click on "Select all" control
$('#select-all').on('click', function(){
    var rows = table.rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
});

// Handle click on checkbox to set state of "Select all" control
$('#product_edit_price_table tbody').on('change', 'input[type="checkbox"]', function(){
    if(!this.checked){
      var el = $('#select-all').get(0);
      if(el && el.checked && ('indeterminate' in el)){
          el.indeterminate = true;
      }
    }
});
/************************* */

mySelect.addEventListener('change', function () {
    const selectedValue = mySelect.value;
    console.log('Valor seleccionado:', selectedValue);
    // You can now use `selectedValue` to make AJAX, change UI, etc
    get_data("/get_product_category/",selectedValue).then((resp) => {
        console.log(resp);
        fnShowProducts(resp);
    })
});

get_data = async (path,id) => {
    try {
        let response = await fetch(`${path}${id}`);
        let resp = await response.json()
        console.log(resp) 
        return resp;
    } catch (error) {
        console.log(error);
    }
}

fnShowProducts = (resp) => {
    let products = resp.data;
    console.log(products);
    listProducts.innerHTML = "";
    products.forEach(element => {
        console.log(element)
        listProducts.innerHTML += 
        `
         <tr>
            <td>Mark</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
            <td>@mdo</td>
            <td>@mdo</td>
        </tr>
        `
    });
}