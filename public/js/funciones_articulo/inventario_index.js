const fnShowModalBarcode = (productoId, productoNombre) => {
    // Seteamos el ID oculto
    document.querySelector("#productoId").value = productoId;
    // Mostramos el nombre del producto en el input readonly del modal
    document.querySelector("#productoNombre").value = productoNombre;
}