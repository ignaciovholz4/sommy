console.log("main");
/**functiones de validacion de caracteres maxino 3 decimales en los input*/
function filterFloat(evt,input){
    // Backspace = 8, Enter = 13, ‘0′ = 48, ‘9′ = 57, ‘.’ = 46, ‘-’ = 43
    var key = window.Event ? evt.which : evt.keyCode;    
    var chark = String.fromCharCode(key);
    var tempValue = input.value+chark;
    if(key >= 48 && key <= 57){
        if(filter(tempValue)=== false){
            return false;
        }else{       
            return true;
        }
    }else{
          if(key == 8 || key == 13 || key == 0) {     
              return true;              
          }else if(key == 46){
                if(filter(tempValue)=== false){
                    return false;
                }else{       
                    return true;
                }
          }else{
              return false;
          }
    }
}
function filter(__val__){
    var preg = /^([0-9]+\.?[0-9]{0,3})$/; 
    if(preg.test(__val__) === true){
        return true;
    }else{
       return false;
    }
    
}
/**Function que valida 2 numeros decimal de los input*/
function filterFloatdecimal2(evt,input){
    // Backspace = 8, Enter = 13, ‘0′ = 48, ‘9′ = 57, ‘.’ = 46, ‘-’ = 43
    var key = window.Event ? evt.which : evt.keyCode;    
    var chark = String.fromCharCode(key);
    var tempValue = input.value+chark;
    if(key >= 48 && key <= 57){
        if(filter2(tempValue)=== false){
            return false;
        }else{       
            return true;
        }
    }else{
          if(key == 8 || key == 13 || key == 0) {     
              return true;              
          }else if(key == 46){
                if(filter2(tempValue)=== false){
                    return false;
                }else{       
                    return true;
                }
          }else{
              return false;
          }
    }
}
function filter2(__val__){
    var preg = /^([0-9]+\.?[0-9]{0,2})$/; 
    if(preg.test(__val__) === true){
        return true;
    }else{
       return false;
    }
    
}

/**function para obtener la fecha actual*/
function hoyFecha(){
    var hoy = new Date();
        var dd = hoy.getDate();
        var mm = hoy.getMonth()+1;
        var yyyy = hoy.getFullYear();
        
        //dd = addZero(dd);
        //mm = addZero(mm);
 
        var fetc = dd+'-'+mm+'-'+yyyy;
        $("#serie_comprobante").val(fetc);
        $("#num_comprobante").val(fetc);
        /**para la vista ingreso*/    
}

const formatDate = (dateString) => {
  let date = new Date(dateString);
  const year = date.getFullYear();
  const month = ('0' + (date.getMonth() + 1)).slice(-2); 
  const day = ('0' + date.getDate()).slice(-2);
  const hour = date.getHours();
  const minutes = date.getMinutes();
  const seconds = date.getSeconds();
  return `${day}/${month}/${year} ${hour}:${minutes}:${seconds}`;
}

// Solo permite ingresar numeros.
function soloNumeros(e){
    var key = window.Event ? e.which : e.keyCode
    return (key >= 48 && key <= 57)
}


const sweetError = (message) => {
    Swal.fire({
    title: "Oops...",
    text: message,
    type: "error"
    });
}

const sweetSuccess = (message) => {
    Swal.fire({
    title: "Exito",
    text: message,
    type: "success"
    });
}

const sweetErrorHtml = (message) => {
    Swal.fire({
    title: "Oops...",
    html: message,
    type: "error"
    });
}

/******************************/

function formatNumberPais(number){
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

// Inicializa SlimSelect con búsqueda por texto y código
function enableSelect2(selector) {
    $(selector).select2({
        placeholder: "Seleccione un artículo",
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron coincidencias";
            },
            searching: function() {
                return "Buscando...";
            }
        },
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            const term = params.term.toLowerCase();
            const text = (data.text || '').toLowerCase();
            const value = (data.id || '').toLowerCase();
            if (text.includes(term) || value.includes(term)) {
                return data;
            }
            return null;
        }
    });
}