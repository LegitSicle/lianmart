const selectAllBox = document.getElementById("selectAll");

function checkAll() {
    const container = document.getElementById(selectAllBox.dataset.container);
    const checkboxes = container.querySelectorAll('input[type="checkbox"]')
    for (const checkbox of checkboxes) {
        checkbox.checked = selectAllBox.checked;
    }
}

selectAllBox.addEventListener("click", checkAll);