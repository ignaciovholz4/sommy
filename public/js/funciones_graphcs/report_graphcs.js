import { graphics_show1 } from "../export_funcion/graphics.js";

let section_show_graph = document.querySelector(".section-graph");
let section_day = document.querySelector(".section-dias");
let section_month = document.querySelector(".section-mes");

class graph {
  msg_error(data){
    Swal.fire({
      type: 'error',
      title: 'Oops...',
      text: data.alert,
    });
  }

}

class date_graph extends graph {
  get_data = async (url, form) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        body: new FormData(form),
      });

      let data = await seend.json();
      let resp = data.estatus;

      switch (resp) {
        case 1:
          if (data.accion == "get_for_day") {
            console.log(data);
            this.show_graph_days(data);
          }
          if (data.accion == "get_for_month") {
              console.log(data);
              this.show_graph_month(data);
          }
        break;
        case 0:
          alert(data.mensaje);
        break;
        case "errorvalidacion":
          super.msg_error(data);
        break;
        default:
          break;
      }

    } catch (error) {
        console.log(error);
    }
  };

  show_graph_month = (data) =>{
    section_show_graph.style.display = "block";
    let get_month_cant = data.dates_month;
    let get_month_cant_ecommerce = data.dates_month_ecommerce;

    let etiqueta = [];
    let cant = [];
    let etiqueta_ecommerce = [];
    let cant_ecommerce = [];
    let temp1 = [];
    let temp2 = [];

    if(get_month_cant_ecommerce.length > get_month_cant.length ) {//condicion 1
      get_month_cant_ecommerce.forEach((row, index) => {
        etiqueta_ecommerce.push(row.months);
        cant_ecommerce.push(row.total_sales_ecommerce);
        get_month_cant.forEach((p) => {
          if(p.months != row.months){
            console.log(row)
            temp1.push({
              "total_sales": 0.00,
              "months": row.months
            });
          }
        });
      });
      /*****************************************/
      const mergedArray = [...get_month_cant, ...temp1];
      console.log(mergedArray);
      mergedArray.forEach((element) => {
        etiqueta.push(element.months);
        cant.push(element.total_sales);
      });
    }
    /****************************** */
    if(get_month_cant.length > get_month_cant_ecommerce.length ) {//condicion 2
      get_month_cant.forEach((element) => {
        etiqueta.push(element.months);
        cant.push(element.total_sales);
        get_month_cant_ecommerce.forEach((p) => {
          if(p.months != element.months){
            console.log(element)
            temp2.push({
              "total_sales": 0.00,
              "months": element.months
            });
          }
        });
      });
      /***************************** */
      const mergedArray2 = [...get_month_cant_ecommerce, ...temp2];
      console.log(mergedArray2);
      mergedArray2.forEach((row, index) => {
        etiqueta_ecommerce.push(row.months);
        cant_ecommerce.push(row.total_sales_ecommerce);
      });
    }
    /****************************** */
    if(get_month_cant_ecommerce.length == get_month_cant.length ) { //condicion 3
      get_month_cant.forEach((element) => {
        etiqueta.push(element.months);
        cant.push(element.total_sales);
      });
      get_month_cant_ecommerce.forEach((row, index) => {
        etiqueta_ecommerce.push(row.months);
        cant_ecommerce.push(row.total_sales_ecommerce);
      });
    }

    let show_sales_total = document.querySelector(".show-sale-general");
    show_sales_total.innerHTML = "";
    show_sales_total.innerHTML = "$ "+data.total_general;
    let show_graph= document.querySelector(".div-content-graph")
    show_graph.innerHTML = '&nbsp;';
    show_graph.innerHTML = "<canvas id='search-sales-chart' height='200'></canvas>";
    let idgraphics = document.querySelector("#search-sales-chart");
    let labels = etiqueta;
    let type_graphics = "bar";
    let newdata1 = {};
    newdata1 = {
      label: "Ventas por mes",
      backgroundColor: "#00FA9A",
      borderColor: "#007bff",
      data: cant,
    };
    /***** for ecommerce******* */
    let labelsEcommerce = etiqueta_ecommerce;
    let newdata2 = {};
    newdata2 = {
      label: "Ventas por mes ecommerce",
      backgroundColor: "#ced4da",
      borderColor: "#ced4da",
      data: cant_ecommerce,
    };
    console.log(labelsEcommerce.length)
    if(labelsEcommerce.length > labels.length ) labels = labelsEcommerce;
    if(labelsEcommerce.length < labels.length ) labels = labels;
    if(labelsEcommerce.length = labels.length ) labels = labels;
    graphics_show1(idgraphics, labels, newdata1, type_graphics,newdata2);
  }

  show_graph_days = (data) =>{
    section_show_graph.style.display = "block";
    let get_date_cant = data.dates;
    let get_date_cant_ecommerce = data.dates_ecommerce;
    let etiqueta = [];
    let cant = [];
    let etiqueta_ecommerce = [];
    let cant_ecommerce = [];

    let arrayObject1 = [];
    let arrayObject2 = [];

    if(get_date_cant_ecommerce.length > get_date_cant.length ) {//condicion 1
      get_date_cant_ecommerce.forEach((row, index) => {
        etiqueta_ecommerce.push(row.fecha);
        cant_ecommerce.push(row.total_sales_ecommerce);
        get_date_cant.forEach((d) => {
          if(d.fecha != row.fecha){
            console.log(row)
            arrayObject1.push({
              "fecha": row.fecha,
              "total_sales": 0.00,
            });
          }
        });
      });
      /*****************************************/
      const mergedArray1 = [...get_date_cant, ...arrayObject1];
      console.log(mergedArray1);
      mergedArray1.forEach((element) => {
        etiqueta.push(element.fecha);
        cant.push(element.total_sales);
      });
      console.log(etiqueta);
      console.log(cant);
    }

    if(get_date_cant.length > get_date_cant_ecommerce.length ) {//condicion 2
      get_date_cant.forEach((element) => {
        etiqueta.push(element.fecha);
        cant.push(element.total_sales);
        get_date_cant_ecommerce.forEach((d) => {
          if(d.fecha != element.fecha){
            console.log(element)
            arrayObject2.push({
              "fecha": element.fecha,
              "total_sales": 0.00,
            });
          }
        });
      });
      /***************************** */
      const mergedArray2 = [...get_date_cant_ecommerce, ...arrayObject2];
      console.log(mergedArray2);
      mergedArray2.forEach((element) => {
        etiqueta_ecommerce.push(element.fecha);
        cant_ecommerce.push(element.total_sales);
      });
    }

    if(get_date_cant_ecommerce.length == get_date_cant.length ) { //condicion 3
      get_date_cant.forEach((element) => {
        etiqueta.push(element.fecha);
        cant.push(element.total_sales);
      });
      console.log(etiqueta);
      console.log(cant);
      get_date_cant_ecommerce.forEach((row, index) => {
        console.log(row);
        etiqueta_ecommerce.push(row.fecha);
        cant_ecommerce.push(row.total_sales_ecommerce);
      });
      console.log(etiqueta_ecommerce);
      console.log(cant_ecommerce);
    }

    let show_sales_total = document.querySelector(".show-sale-general");
    show_sales_total.innerHTML = "";
    show_sales_total.innerHTML = "$ "+data.total_general;
    let show_graph= document.querySelector(".div-content-graph")
    show_graph.innerHTML = '&nbsp;';
    show_graph.innerHTML = "<canvas id='search-sales-chart' height='200'></canvas>";
    let idgraphics = document.querySelector("#search-sales-chart");
    let labels = etiqueta;
    let type_graphics = "bar";
    let newdata1 = {};
    newdata1 = {
      label: "Ventas por dia",
      backgroundColor: "#00FA9A",
      borderColor: "#007bff",
      data: cant,
    };
    /*******FOR ECOMMERCE*********** */
    let labelsEcommerce = etiqueta_ecommerce;
    let newdata2 = {};
    newdata2 = {
      label: "Ventas por dia ecommerce",
      backgroundColor: "#ced4da",
      borderColor: "#ced4da",
      data: cant_ecommerce,
    };
    console.log(cant_ecommerce);
    if(labelsEcommerce.length > labels.length ) labels = labelsEcommerce;
    if(labelsEcommerce.length < labels.length ) labels = labels;
    if(labelsEcommerce.length = labels.length ) labels = labels;
    graphics_show1(idgraphics, labels, newdata1, type_graphics,newdata2);
  }
}
/**FUNCTION GET THE GRAPH FOR DAYS */
let form_date = document.querySelector("#get-date-form");
let btn_graph = document.querySelector("#btn-send-date");
btn_graph.addEventListener("click", (e) => {
  e.preventDefault();
  let url = "/getdatagraph";
  let dateget = new date_graph();
  dateget.get_data(url, form_date);
});

/** */
let btn_mes = document.querySelector("#btn-send-mes");
let form_mes = document.querySelector("#get-mes-form");
btn_mes.addEventListener("click", (e) => {
  e.preventDefault();
  let url = "/getmesgraph";
  let mesget = new date_graph();
  mesget.get_data(url, form_mes);
});

/**TIPO DATE RADIO SHOW FORM */
let radio_date = document.getElementsByName("graphdate");
radio_date.forEach(element => {
  element.addEventListener("click", (e) =>{
    console.log(element.value)
    let value_check = element.value;
    switch (value_check) {
      case "mes":
        form_date.reset();
        section_day.style.display="none";
        section_month.style.display="block";
        section_show_graph.style.display = "none";
      break;
      case "dia":
        form_mes.reset();
        section_month.style.display="none";
        section_day.style.display="block";
        section_show_graph.style.display = "none";
      break;
    
      default:
        break;
    }
  });
});


///https://www.youtube.com/watch?v=MCieBWwddEY