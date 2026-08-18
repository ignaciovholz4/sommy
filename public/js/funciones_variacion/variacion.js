let tagsVariantes = [];
const inputAddVariacion = document.getElementById('addVariaciones');
const showTags = document.querySelector('.showTags');
const btnSaveMarca = document.getElementById('btnsavemarca');
const formSaveMarca = document.getElementById('save_variante');
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': token
        }
    });
    $(function() {
    $('#variante_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showvariacion',
            type: 'GET',
           },
           columns: [
                   { data: 'id', name: 'id'},
                   { data: 'name', name: 'name' },
                   { data: 'option_type', name: 'option_type' },
                   { data: 'registration_date', name: 'registration_date' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});

const idmodalsavemarca = document.querySelector("#ModalVariante");
const myModalsavemarca = new bootstrap.Modal(idmodalsavemarca);
const btnshowmodalsavemarca = document.querySelector("#btnshowModalVariante");
btnshowmodalsavemarca.addEventListener("click", (e) => {
  e.preventDefault();
  console.log("open modal save")
  tagsVariantes = [];//reset the array
  createTagElement();
  formSaveMarca.reset();
  myModalsavemarca.show();//show the modal
  document.getElementById("id").value = Number(0);
});


btnSaveMarca.addEventListener("click", (e) => {
    e.preventDefault();
    console.log(tagsVariantes)
    const formData = new FormData(formSaveMarca);
    //const name = formData.get('name');
    formData.append('variaciones', JSON.stringify(tagsVariantes));
    const data = Object.fromEntries(formData.entries());
    console.log(data);
    //console.log('Name:', name);
    fetch('/savevariacion', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json(); // Parse the JSON response from the server
  })
  .then(result => {
    console.log('Success:', result); // Log the server response
            if (result.estado == 1) {
                $('#save_marca').trigger("reset");
                myModalsavemarca.hide();
                //console.log(result.mensaje);
                var TableRefresh = $('#variante_table').dataTable(); 
                TableRefresh.fnDraw(false);
                //$('#btnsavemarca').html('Guardar');
                toastr.success(result.mensaje);
            }else if (result.estado == 0) {
                console.log(result.mensaje);
                //$('#btnsavemarca').html('Guardar');
                toastr.error(result.mensaje);
            }
            if(result.error){
                saveprintErrorMsg(result.error);
                window.setTimeout(function() { 
                    $(".print-save-error-msg").slideUp(function() { 
                    });
                },  5000);
            }
  })
  .catch(error => {
    console.error('Error:', error); // Log any errors
  });
        
});

/**FUNCTION QUE PINTA LAOS ERRORES AL GUARDAR LA INFORMACION*/
let saveprintErrorMsg = (msg) => {
    console.log(msg)
    $(".print-save-error-msg").find("ul").html('');
    $(".print-save-error-msg").css('display','block');
    $.each(msg, function(key, value) {
        $(".print-save-error-msg").find("ul").append('<li>'+value+'</li>');

    });
}

    /**GUARDAR LOS DATOS DE LA MARCA*/
    $("#save_marca").on('submit',function(e){
        e.preventDefault(e);
        var datos = $(this).serialize();
        //alert(datos);
        $("#btnsavemarca").html('Enviando datos ...')
        url_ingreso = "/savemarca";
        $.post(url_ingreso,datos,function (result) {
            // var mensaje = result.exito;
            // console.log(mensaje);
            if($.isEmptyObject(result.error)){
                //alert("jajaj"+ data.success);
            }else{
                 /**LLAMA LA FUNCTION Y PINTA LOS ERRORES POSIBLES QUE SE GENEREN EN LA VALIDACION DE EL FORMULARIO */
                 saveprintErrorMsg(result.error);
                 $('#btnsavemarca').html('Guardar');
                 window.setTimeout(function() { 
                     $(".print-save-error-msg").slideUp(function() { 
                     });
                 },  5000);
            }

            if (result.estado == 1) {
                $('#save_marca').trigger("reset");
                myModalsavemarca.hide();
                //console.log(result.mensaje);
                var TableRefresh = $('#variante_table').dataTable(); 
                TableRefresh.fnDraw(false);
                //$('#btnsavemarca').html('Guardar');
                toastr.success(result.mensaje);
            }else if (result.estado == 0) {
                console.log(result.mensaje);
                //$('#btnsavemarca').html('Guardar');
                toastr.error(result.mensaje);
            }
            
        }).fail(function (error) {           
            console.log(error);
            $('#btnsavemarca').html('Guardar');
            toastr.error("Error: Ocurrio un error inesperado, revisa el codigo");
        });
        
    });


/**FUNCION OBTIENE LOS DATOS DE LA MARCA SELECCIONADA EN EL BUTTON*/
var edit_variante = (id) => {
    $.get('/variacion-list/'+id, function (data) {
        console.log(data);
        console.log(data.variantes);
        let dataVariante = data.variacion;
        tagsVariantes = [];
        createTagElement();
        data.variantes.forEach(element => {
            console.log(element);
            tagsVariantes.push(element.name);
            createTagElement();
        });
        $('#ModalVariante').modal('show');
        document.getElementById('id').value = Number(dataVariante.id);
        document.getElementById('option_type').value = dataVariante.option_type;
        document.getElementById('name').value = dataVariante.name;
    });
}

class marca{
    constructor(){}

    refresh_table_marca(){
        var TableRefresh = $('#variante_table').dataTable(); 
        TableRefresh.fnDraw(false);
    }
}

class acciones_marca extends marca{
    delete_marca = async (url,form) => {
        try {
            let response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token
            },
            method: 'POST',
            body: form
            });
            let data = await response.json();
            //console.log(data);
            let resp = data.estado;
            switch (resp) {
                case 1:
                    Swal.fire(
                        'Exito!',
                        data.mensaje,
                        'success'
                    )
                    super.refresh_table_marca();
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

/**FUNCTION PARA ELIMINAR LA MARCA*/
const delete_marca = (id) => {
  Swal.fire({
    title: 'Estas seguro de eliminar la marca?',
    text: "El color ya no estara disponible!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.value) {
      let url = "/deletemarca";
      let formdown = new FormData();
      formdown.append('id', id);
      const down= new  acciones_marca();
      down.delete_marca(url,formdown);
    }

  });
}
/*********ADD CHIPS VARIACIONES******** */
inputAddVariacion.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' || event.key === ',') {
        const tag = inputAddVariacion.value.trim();
        if (tag && !tagsVariantes.includes(tag)) {
            tagsVariantes.push(tag);
            createTagElement();
            inputAddVariacion.value = '';
        }
        console.log(tagsVariantes);
    }
});

/********ADD VARIACIONES TO ARRAY**********/
const createTagElement = () => {
    showTags.innerHTML = '';
    tagsVariantes.forEach((element) => {
        console.log(element);
        showTags.innerHTML += 
        `
            <button type="button" class="btn btn-primary">
                ${element} <span class="badge bg-secondary" ><i onClick="fnRemoveTag('${element}');" class="fas fa-times"></i></span>
            </button>
        `;
    });
}

const fnRemoveTag = (tag) => {
    console.log(tag);
    tagsVariantes = tagsVariantes.filter(t => t !== tag);
    createTagElement();
    console.log(tagsVariantes)
}