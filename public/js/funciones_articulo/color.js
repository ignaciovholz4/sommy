
$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    $('#color_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showcolor',
            type: 'GET',
           },
           columns: [
                   { data: 'id', name: 'id'},
                   { data: 'name', name: 'name' },
                   { data: 'hexadecimal', name: 'hexadecimal' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});
const idmodalsavecolor = document.querySelector("#ModalColor");
const myModalsavecolor = new bootstrap.Modal(idmodalsavecolor);
const btnshowmodalsavecolor = document.querySelector("#btnshowmodalColor");
const findColor = document.querySelector("#findColor");
const hexadecimal = document.querySelector("#hexadecimal");
btnshowmodalsavecolor.addEventListener("click", (e) => {
  e.preventDefault();
  myModalsavecolor.show();
  $("#save_color").trigger("reset");
  document.getElementById("id").value = Number(0);
});

    /**GUARDAR LOS DATOS DE LA COLOR*/
    $("#save_color").on('submit',function(e){
        e.preventDefault(e);
        var datos = $(this).serialize();
        //alert(datos);
        $("#btnsavecolor").html('Enviando datos ...')
        url_ingreso = "/savecolor";
        $.post(url_ingreso,datos,function (result) {
            // var mensaje = result.exito;
            // console.log(mensaje);
            if($.isEmptyObject(result.error)){
                //alert("jajaj"+ data.success);
            }else{
                 /**LLAMA LA FUNCTION Y PINTA LOS ERRORES POSIBLES QUE SE GENEREN EN LA VALIDACION DE EL FORMULARIO */
                 saveprintErrorMsg(result.error);
                 $('#btnsavecolor').html('Guardar');
                 window.setTimeout(function() { 
                     $(".print-save-error-msg").slideUp(function() { 
                     });
                 },  5000);
            }

            if (result.estado == 1) {
                $('#save_color').trigger("reset");
                myModalsavecolor.hide();
                //console.log(result.mensaje);
                var TableRefresh = $('#color_table').dataTable(); 
                TableRefresh.fnDraw(false);
                $('#btnsavecolor').html('Guardar');
		toastr.success(result.mensaje);
            }else if (result.estado == 0) {
                console.log(result.mensaje);
                $('#btnsavecolor').html('Guardar');
                toastr.error(data.mensaje);
            }
            
        }).fail(function (error) {           
            console.log(error);
            $('#btnsavecolor').html('Guardar');
            toastr.error("Error: Ocurrio un error inesperado, revisa el codigo");
        });
        
    });


/**FUNCTION QUE PINTA LAOS ERRORES AL GUARDAR LA INFORMACION*/
var saveprintErrorMsg = (msg) => {
    $(".print-save-error-msg").find("ul").html('');
    $(".print-save-error-msg").css('display','block');
    $.each(msg, function(key, value) {
        $(".print-save-error-msg").find("ul").append('<li>'+value+'</li>');

    });

}

/**FUNCION OBTIENE LOS DATOS DEL COLOR SELECCIONADA EN EL BUTTON*/
var edit_color = (id) => {
    $.get('/color-list/'+id, function (data) {
        $('#ModalColor').modal('show');
        document.getElementById('id').value = Number(data.id);
        document.getElementById('nombre').value = data.name;
        document.getElementById('hexadecimal').value = data.hexadecimal;
        findColor.value = data.hexadecimal;
    });
}

class color{
    constructor(){}

    refresh_table_color(){
        var TableRefresh = $('#color_table').dataTable(); 
        TableRefresh.fnDraw(false);
    }
}

class acciones_color extends color{
    delete_color = async (url,form) => {
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
            //console.log(data);
            let resp = data.estado;
            switch (resp) {
                case 1:
                    Swal.fire(
                        'Exito!',
                        data.mensaje,
                        'success'
                    )
                    super.refresh_table_color();
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

/**FUNCTION PARA ELIMINAR EL COLOR*/
const delete_color = (id) => {
  Swal.fire({
    title: 'Estas seguro de eliminar el color?',
    text: "El color ya no estara disponible!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.value) {
      let url = "/deletecolor";
      let formdown = new FormData();
      formdown.append('id', id);
      const down= new  acciones_color();
      down.delete_color(url,formdown);
    }

  });
}

findColor.addEventListener('input', (event) => {
    console.log(event.target.value);
    hexadecimal.value = event.target.value;
});