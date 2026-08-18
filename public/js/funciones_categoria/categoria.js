
let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    $('#categoria_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showcategoria',
            type: 'GET',
           },
           columns: [
                   { data: 'idcategoria', name: 'idcategoria'},
                   { data: 'nombre', name: 'nombre' },
                   { data: 'descripcion', name: 'descripcion' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});

$(document).ready(function(){
    const idmodalsavecategory = document.querySelector("#ModalCategoria");
    const myModalsavecategory = new bootstrap.Modal(idmodalsavecategory);
    const btnshowmodalsavecategory = document.querySelector("#btnshowmodalcategory");
    btnshowmodalsavecategory.addEventListener('click', (e) =>{
        e.preventDefault();
        myModalsavecategory.show();
    })
    /**GUARDAR LOS DATOS DE LA CATEGORIA*/
    $("#save_categorie").on('submit', async function (e){
        e.preventDefault(e);
        const imageInput = document.getElementById("file");
        const nombre = document.getElementById("nombre");
        const descripcion = document.getElementById("descripcion");
        const formData = new FormData();
        formData.append("imagen", imageInput.files[0]); // Get the selected file
        formData.append("descripcion", descripcion.value);
        formData.append("nombre", nombre.value);
        console.log(imageInput.files[0]);
        $("#btnsavecategoria").html('Enviando datos ...')
        url = "/savecategoria";
        try {
            const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token
            },
            method: "POST",
            body: formData,
            });  
            
            let data = await response.json();
            console.log(data);
            if(data.status === 0) {
                saveprintErrorMsg(data.error);
                $('#btnsavecategoria').html('Guardar');
                window.setTimeout(function() { 
                    $(".print-save-error-msg").slideUp(function() { 
                    });
                },  5000);
            }
            if (data.status === 1) {
                $('#save_categorie').trigger("reset");
                myModalsavecategory.hide();
                var TableRefresh = $('#categoria_table').dataTable(); 
                TableRefresh.fnDraw(false);
                $('#btnsavecategoria').html('Guardar');
            }
        } catch (error) {
            console.log(error);
            $('#btnsavecategoria').html('Guardar');
        }
    });

    /**FUNCTION QUE PERMITE ACTUALIZAR LA INFORMACION DE LA CATEGORIA*/
    $("#update_categorie").on('submit', async function (e) {
        e.preventDefault(e);
        const imageInput = document.getElementById("upfile");
        //console.log(imageInput);
        const nombre = document.getElementById("upnombre");
        const descripcion = document.getElementById("updescripcion");
        const upid = document.getElementById("upid");
        const formData = new FormData();
       formData.append("upimagen", imageInput.files[0]); // Get the selected file
        formData.append("updescripcion", descripcion.value);
        formData.append("upnombre", nombre.value);
        formData.append("upid", upid.value);
        //console.log(imageInput.files[0]);
       //var updatedatos = $(this).serialize();
       $('#btnupdatecategoria').html('Enviando datos ...');
       try {
        
           let url = "/categoriaupdate";
            const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token
            },
            method: "POST",
            body: formData,
            });  
            
            let data = await response.json();
            console.log(data);
            if (data.estado == 1) {
                toastr.success(data.mensaje);
                $('#update_categorie').trigger("reset");
                $('#ModalCategoriaUpdate').modal('hide');
                var TableRefresh = $('#categoria_table').dataTable();
                TableRefresh.fnDraw(false);
                $('#btnupdatecategoria').html('Actualizar'); 
                   
            }else if (data.estado == 0) {
                toastr.error(data.mensaje);
                $('#btnupdatecategoria').html('Actualizar');
            }
       } catch (error) {
        
       }

       /*$.ajax({
            data: updatedatos,
            url: "/categoriaupdate",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                if($.isEmptyObject(data.error)){
                }else{
                    updateprintErrorMsg(data.error);
                    $('#btnupdatecategoria').html('Actualizar');
                    window.setTimeout(function() { 
                        $(".print-update-error-msg").slideUp(function() { 
                        });
                    },  5000);
                }
                if (data.estado == 1) {
                    toastr.success(data.mensaje);
                    $('#update_categorie').trigger("reset");
                    $('#ModalCategoriaUpdate').modal('hide');
                    var TableRefresh = $('#categoria_table').dataTable();
                    TableRefresh.fnDraw(false);
                    $('#btnupdatecategoria').html('Actualizar'); 
                   
                }else if (data.estado == 0) {
                    toastr.error(data.mensaje);
                    $('#btnupdatecategoria').html('Actualizar');
                }
            },
            error: function (data) {
                $('#btnupdatecategoria').html('Actualizar');
                console.log('Error:', data);
                toastr.error("Error: Ocurrio un error inesperado, revisa el codigo");
            }    
       });*/
       
    });
   
});
/********************************** */
class categoria{
    constructor(){}

    refresh_table_category(){
        var TableRefresh = $('#categoria_table').dataTable(); 
        TableRefresh.fnDraw(false);
    }
}

class acciones_categoria extends categoria{
    delete_categoria = async (url,form) => {
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
                    super.refresh_table_category();
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
/**FUNCTION PARA ELIMINAR LA CATEGORIA*/
const delete_categoria = (id) => {
  Swal.fire({
    title: 'Estas seguro de eliminar la categoria?',
    text: "La categoria ya no estara disponible!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.value) {
      let url = "/deletecategoria";
      let formdowncategory = new FormData();
      formdowncategory.append('id', id);
      const downcategory = new  acciones_categoria();
      downcategory.delete_categoria(url,formdowncategory);
    }

  });
}

//var delete_categoriass = (id) => {
    //if (confirm('Estas seguro de eliminar la categoria')) {
        //$.ajax({
            //type:'GET',
            //url: '/deletecategoria/'+id,
            //success: function (data) {
                //if (data.estado == 1) {
                    //var TableRefresh = $('#categoria_table').dataTable(); 
                    //TableRefresh.fnDraw(false);
                    //toastr.success(data.mensaje);
                //}else if (data.estado == 0) {
                    //toastr.error(data.mensaje);
                //}
                
            //},
            //error: function (data) {
                //console.log('Error:', data); 
                //toastr.error("Error: Ocurrio un error inesperado, revisa el codigo");
            //}
            
        //});
    //}
//}

/**FUNCION OBTIENE LOS DATOS DE LA CATEGORIA SELECCIONADA EN EL BUTTON*/
var edit_categoria = (id) => {
    //alert('edit '+ id);
    $.get('/categoria-list/'+id, function (data) {
        // $('#CategoriaLabel').html("Editar la categoria");
        console.log(data);
        $('#ModalCategoriaUpdate').modal('show');
        document.getElementById('upid').value = data.idcategoria;
        document.getElementById('upnombre').value = data.nombre;
        document.getElementById('updescripcion').value = data.descripcion;
        const imgPreview = document.getElementById('imgPreview');
        imgPreview.innerHTML = "";
        imgPreview.innerHTML += 
        `
            <span>Tamaño recomendado:	150px 127px</span><br>
            <div class="input-group mb-3">
                <input type="file" id="upfile" name="upimagen" class="form-control" accept="image/*">
                <div class="input-group-append">
                    <div class="input-group-text style-icon-fas">
                        <i class="fas fa-image"></i>
                    </div>
                </div>
            </div>
        <div class="form-group" style="margin-top:8px;">
          <figure class="figure">
            <img src="../imagenes/categorias/${data.name_imagen}" height="80px" width="90px" class="">
            <figcaption class="figure-caption">Imagen actual</figcaption>
          </figure>
        </div>
        `
        //console.log(data);
    });
}

/**FUNCTION QUE PINTA LOS ERRORES AL ACTUALIZAR INFORMACION*/
var updateprintErrorMsg = (msg) => {
    $(".print-update-error-msg").find("ul").html('');
    $(".print-update-error-msg").css('display','block');
    $.each(msg, function(key, value) {
        $(".print-update-error-msg").find("ul").append('<li>'+value+'</li>');

    });

}
/**FUNCTION QUE PINTA LAOS ERRORES AL GUARDAR LA INFORMACION*/
var saveprintErrorMsg = (msg) => {
    $(".print-save-error-msg").find("ul").html('');
    $(".print-save-error-msg").css('display','block');
    $.each(msg, function(key, value) {
        $(".print-save-error-msg").find("ul").append('<li>'+value+'</li>');

    });

}
