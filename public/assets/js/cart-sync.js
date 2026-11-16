const selectAllBox = document.getElementById("selectAll");

function changeNumberInput(event) {
    const button = event.target.closest(".qty button");
    if (!button) return;

    const qtyContainer = button.closest(".qty");
    const input = qtyContainer.querySelector(".qty-input");
    if (!input) return;

    let currentValue = parseInt(input.value) || 1;
    const min = parseInt(input.getAttribute("min")) || 1;
    const max = parseInt(input.getAttribute("max")) || Infinity;
    
    let newValue = currentValue;
    let hasChanged = false;

    if (button.classList.contains("plus")) {
        if (currentValue < max) {
            newValue = currentValue + 1;
            hasChanged = true;
        }
    } else if (button.classList.contains("minus")) {
        if (currentValue > min) {
            newValue = currentValue - 1;
            hasChanged = true;
        }
    }
    if (hasChanged) {
        input.value = newValue;
        input.dispatchEvent(new Event("change", { bubbles: true }));
    }
}

function syncBackend(event) {
    const input = event.target.closest(".qty-input");
    if (!input) return;

    const newQuantity = parseInt(input.value);
    const min = parseInt(input.getAttribute("min")) || 1;
    const max = parseInt(input.getAttribute("max")) || Infinity;

    if (isNaN(newQuantity) || newQuantity < min || newQuantity > max) {
        return;
    }

    const productId = parseInt(input.dataset.id);
    fetch("/lianmart/api/cart/update", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePage(data);
        } else {
            console.error("Failed to update cart: " + data.message);
        }
    })
    .catch(error => {
        console.error("Network or server error during cart sync:", error);
    });
}

function updatePage(data) {
    const totalQuantity = document.getElementById("totalQuantity");
    totalQuantity.textContent = data.quantity;

    const subtotal = document.getElementById("subtotalItems");
    subtotal.textContent = `\u20B1${data.subtotal.toFixed(2)}`;
}

document.addEventListener("click", changeNumberInput);
document.addEventListener("change", syncBackend);