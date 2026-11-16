const amountInput = document.querySelector('.amount-input');
const incrementButton = document.querySelector('.plus-button');
const decrementButton = document.querySelector('.minus-button');

incrementButton.addEventListener('click', () => {
    const currentValue = parseInt(amountInput.value);
    const max = amountInput.getAttribute("max") || Infinity;
    if (currentValue < max) {
        amountInput.value = currentValue + 1;
    } 
});

decrementButton.addEventListener('click', () => {
    const currentValue = parseInt(amountInput.value);
    const min = amountInput.getAttribute("min") || 1;
    if (currentValue > min) {
        amountInput.value = currentValue - 1;
    }
});

amountInput.addEventListener('input', () => {
    let currentValue = parseInt(amountInput.value);
    const max = amountInput.getAttribute("max") || Infinity;
    const min = amountInput.getAttribute("min") || 1;
    if (isNaN(currentValue) || currentValue < min) {
        amountInput.value = min;
    } else if (currentValue > max) {
        amountInput.value = max;
    }
});