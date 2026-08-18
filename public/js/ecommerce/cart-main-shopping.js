let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
/*const fnListCartProduct = () => {//get the list of products added to cart
    let getDataListShoppingCart = JSON.parse(localStorage.getItem('listShoppingCart')) || [];
    console.log(getDataListShoppingCart);
    return getDataListShoppingCart;
}*/

const saveDataEcommerce = async (formDataEcommerce, url) => {
    try {
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token
            },
            method: "POST",
            body: formDataEcommerce,
        });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const result = await response.json(); // Parse JSON response
        return result;
    } catch (error) {
        console.log(error);
    }
}

const getDataEcommerce = async (formDataEcommerce, url) => {
    try {
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token
            },
            method: "POST",
            body: formDataEcommerce,
        });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const result = await response.json(); // Parse JSON response
        return result;
    } catch (error) {
        console.log(error);
    }
}