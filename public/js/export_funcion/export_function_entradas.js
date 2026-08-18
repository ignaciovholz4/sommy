export class SearchProducto {
  constructor(myurlp, mysearchp, ul_add_lip,idlip) {
    this.url = myurlp;
    this.mysearch = mysearchp;
    this.ul_add_li = ul_add_lip;
    this.idli = idlip;
    this.pcantidad = document.querySelector("#pcantidad");
    this.array = [];
    this.getDataVariants = [];
  }

  InputSearchProduct() {
    this.mysearch.addEventListener("input", (e) => {
      e.preventDefault();
      try {
        let token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
        let minimo_letras = 0; // minimo letras visibles en el autocompletar
        let valor = this.mysearch.value;
        //console.log(valor.length);
        
        const statuscheckBarcode = document.querySelector("#barcodeChecked");
        let statuschecked = statuscheckBarcode.checked;
        
        if (valor.length > minimo_letras && !statuschecked) {
          let datesearh = new FormData();
          datesearh.append("valor", valor);

          fetch(this.url, {
            headers: {
              "X-CSRF-TOKEN": token,
            },
            method: "post",
            body: datesearh,
          })
            .then((data) => data.json())
            .then((data) => {
              console.log("Success:", data);
              this.ShowliProduct(data, valor);
              this.array = data.getprodent;
              //console.log(this.array)

            })
            .catch(function (error) {
              console.error("Error:", error);
            });
        } else {
          this.ul_add_li.style.display = "none";
        }
      } catch (error) {}
    });
  }

  ShowliProduct(data, valor) {
    const checkBarcode = document.querySelector("#barcodeChecked");
    let checked = checkBarcode.checked;
    this.ul_add_li.style.display = "block";
    if (data.estado == 1) {
      if (data.getprodent != "") {
        let arrayp = data.getprodent;
        this.ul_add_li.innerHTML = "";
        let n = 0;
        if (!checked) {
          this.Clearcamposentradaproducto();
          this.Showproductentradas(arrayp,valor,n);
          this.myonclickentrada();
          let adclasli= document.getElementById('1'+this.idli);
          adclasli.classList.add('selected');
        
        }
        
      } else {
        this.Clearcamposentradaproducto();
        this.ul_add_li.innerHTML = "";
        this.ul_add_li.innerHTML += `
                <li>Sin datos</li>
            `;
      }
    }
  }

  Showproductentradas(arrayp,valor,n){
    for (let item of arrayp) {
        n++;
        let typeProduct = "";
        if(item.tipo_producto_id == 1) typeProduct ="simple";
        if(item.tipo_producto_id == 2) typeProduct ="personalizado";
        let nombre = item.nombre;
        const array = `showproductoingreso*/*/*${item.id}*/*/*${item.codigo}*/*/*${item.nombre}*/*/*${item.pcompra}*/*/*${item.pventa}*/*/*${item.tipo_producto_id}`;
        this.ul_add_li.innerHTML +=`
        <li id="${n+this.idli}" value="${item.nombre}" name="${array}" class="list-group-item" data-variantes='${JSON.stringify(item.productVariante)}'='${JSON.stringify(item.productVariante)}' style="width:622px;border:1px solid #f1f1f1;">
                <div class="d-flex flex-row " style="border:1px solid #ccd2db;margin-right:-17px;margin-top:-10px;margin-left:-19px;margin-bottom:-10px;">
                <div class="p-2 text-center" style="border:1px solid #ccd2db;">
                    <img src="${item.img}" class="img-thumbnail" width="50" height="50" >
                </div>
                <div class="p-2">
                        <strong>${nombre.substr(0,valor.length)}</strong>
                        ${nombre.substr(valor.length)}
                        <p class="card-text">Producto: ${typeProduct}</p>
                </div>
                </div>
        </li>
        `;
    }
  }

  myonclickentrada(){
    let listItems = document.querySelectorAll("#autocompleteentrada li");
    listItems.forEach((item, index) => {
        item.addEventListener('click', (event) => {
            const textarray = item.getAttribute('name');
            const dataVariantes  = item.getAttribute('data-variantes');
            console.log(dataVariantes);
            ////console.log(textarray);
            const dividir = textarray.split('*/*/*'); // split string on comma space
            this.Variablesentradaproducto(dividir,dataVariantes);
            this.ul_add_li.innerHTML = "";
        });
    });
  }

  Variablesentradaproducto(dividir,dataVariantes){
    console.log(dividir);
    console.log(dataVariantes);
    console.log(dividir[6]);
    let tipoProductoId =    Number(dividir[6])
    document.querySelector("#tipoProductoId").value = tipoProductoId;
    this.getDataVariants = JSON.parse(dataVariantes);
    console.log(this.getDataVariants);
    if(tipoProductoId == 2){
      let getcompra = document.querySelector("#pprecio_compra");
      let getventa = document.querySelector("#pprecio_venta");
      /************************ */
      const divContentVariantes = document.querySelector("#div-content-variantes");
      const divContentVariaciones = document.querySelector("#div-content-variaciones");
      divContentVariantes.style.display = "block";
      divContentVariaciones.style.display = "block";

      const uniqueData = this.getDataVariants.filter((item, index, self) => 
          index === self.findIndex(t => t.varianteVariacionId === item.varianteVariacionId)
      );

      console.log(uniqueData);
      const selectElement = document.getElementById("selectElementVariant");
      selectElement.innerHTML = "";
      selectElement.innerHTML = ` <option value="">Seleccionar</option>`;  
      uniqueData.forEach(item => {
          const option = document.createElement("option");
          option.value = item.varianteVariacionId;
          option.textContent = `${item.nameVariante}`;
          selectElement.appendChild(option);
      });

      let newData = [];
      const selectElementTipo = document.getElementById("selectElementVariantTipo");
      selectElement.addEventListener('change', (e) => {
          e.preventDefault();
          console.log(selectElement.value);
          newData = this.getDataVariants.filter(item => item.varianteVariacionId === Number(selectElement.value));
          console.log(newData);
          selectElementTipo.innerHTML = "";
          selectElementTipo.innerHTML = ` <option value="">Seleccionar</option>`;  
          newData.forEach(item => {
              const option = document.createElement("option");
              option.value = item.id;
              option.textContent = `${item.nameVariante} - ${item.nameColor}`;
              selectElementTipo.appendChild(option);
          });
          getventa.value = "" 
          getcompra.value = ""
      });

      selectElementTipo.addEventListener('change', (e) => {
          e.preventDefault();
          console.log(selectElementTipo.value);
          let findData = [];
          findData = this.getDataVariants.find((data) => {
              return data.id == Number(selectElementTipo.value) && data.varianteVariacionId == Number(selectElement.value)
          });
          console.log(findData);
          getventa.value = Number(findData.price)
          getcompra.value = Number(findData.pcompra)
      });

      const getid = document.querySelector("#idarticulo").value = dividir[1];
      const getcodigo = document.querySelector("#IngresoCodigoArticulo").value = dividir[2];
      const getnombre = document.querySelector("#pnombrearticulo").value = dividir[3];
      this.mysearch.value = dividir[3];
    }
    if(tipoProductoId == 1){
      const getid = document.querySelector("#idarticulo").value = dividir[1];
      const getcodigo = document.querySelector("#IngresoCodigoArticulo").value = dividir[2];
      const getnombre = document.querySelector("#pnombrearticulo").value = dividir[3];
      const getcompra = document.querySelector("#pprecio_compra").value = dividir[4];
      const getventa = document.querySelector("#pprecio_venta").value = dividir[5];
      this.mysearch.value = dividir[3];
    }

  }

  Clearcamposentradaproducto(){
    const getid = document.querySelector("#idarticulo").value = '';
    const getcodigo = document.querySelector("#IngresoCodigoArticulo").value = '';
    const getnombre = document.querySelector("#pnombrearticulo").value = '';
    const getventa = document.querySelector("#pprecio_venta").value = '';
    const getcompra = document.querySelector("#pprecio_compra").value = '';
  }

  InputKeydownEntradas(id_ulp){
    this.mysearch.addEventListener("keydown", (e) =>{
      switch (e.keyCode) {
        case 40:
          e.preventDefault(); // prevent moving the cursor
          const nextkeycode = document.querySelector(id_ulp+" li:not(:last-child).selected");
          if (nextkeycode !=null) {
            //console.log(nextkeycode);
            nextkeycode.classList.remove('selected');
            ////console.log(lisec);
            const  nextli = nextkeycode.nextElementSibling;
            nextli.classList.add('selected');
            ////console.log(nextli.className);
          }
          
        break;
        case 38:
          e.preventDefault(); // prevent moving the cursor
          const prevkeycode = document.querySelector(id_ulp+" li:not(:first-child).selected");
          if (prevkeycode != null) {
            ////console.log(prevkeycode);
            prevkeycode.classList.remove('selected');
            const prevli = prevkeycode.previousElementSibling;
            prevli.classList.add('selected');
          }
        break;
        case 13:
          const checkBarcode = document.querySelector("#barcodeChecked");
          let checked = checkBarcode.checked;
          if (checked) {
            let token = document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content");
            let datesearh = new FormData();
            datesearh.append("valor", document.querySelector("#BuscarEntradaProducto").value);
            fetch(this.url, {
              headers: {
                "X-CSRF-TOKEN": token,
              },
              method: "post",
              body: datesearh,
            })
            .then(response => response.json())
            .then(result => {
              console.log(result)
              let findprod = result.getprodent[0];
              let dataVariantes = JSON.stringify(findprod.productVariante);
              const array = `showproductoingreso*/*/*${findprod.id}*/*/*${findprod.codigo}*/*/*${findprod.nombre}*/*/*${findprod.pcompra}*/*/*${findprod.pventa}*/*/*${findprod.tipo_producto_id}`;
              const dividirp = array.split('*/*/*'); // split string on comma space
              this.Variablesentradaproducto(dividirp,dataVariantes);
              document.querySelector("#pcantidad").value="1.00";
              document.querySelector("#btn_addentradas").click();
            })
           
          }else{
            e.preventDefault();
            ////console.log("se deshabiloto")
            const liselected = document.querySelector(id_ulp+'>.selected');
            //const text = liselected.textContent;
            const textarray = liselected.getAttribute('name');
            const dividir = textarray.split('*/*/*'); // split string on comma space
            const dataVariantes  = liselected.getAttribute('data-variantes');
            console.log(dataVariantes);
            ////console.log(dividir);
            const validar = dividir[0];
            switch (validar) {
              case "showproductoingreso":
                ////console.log(validar);
                this.Variablesentradaproducto(dividir,dataVariantes);
                document.querySelector(id_ulp).innerHTML = "";
                this.pcantidad.focus();
              break;
              default:
              break;
            }
            return false;
          }
        break;
        default:
        break;
      }
    });
  }

  
}
