const dialogForm = document.querySelector(".modal-form");

const fileInput = document.querySelector("#file-input");
const dropZone = document.querySelector("#drop-zone");

const floatingMainTextFileInput = document.querySelector(".change-to-white");
const floatingSubTextFileInput = document.querySelector(".drag-n-drop-gray-text");

const addProductDialog = document.querySelector("#add-product");
const addProductBtn = document.querySelector("#add-product-btn");
const closeButtons = document.querySelectorAll(".close-dialog-button");

if (addProductDialog.dataset.reopen === "true") {
    addProductDialog.showModal();
}

addProductBtn.addEventListener("click", addProductDialog.showModal);

closeButtons.forEach(button => {
    button.addEventListener("click", function() {
        closeEdit();
        dialogForm.reset();
        dropZone.style.background = "#EBEFF9";
        floatingMainTextFileInput.style.color = "black";
        floatingSubTextFileInput.style.color = "#6f7471";
    });
});

function updateFileInput(image = fileInput.files[0]){
    if (!image) return;
    const imgLink = typeof image === "string" ? image : URL.createObjectURL(image);
    dropZone.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(${imgLink})`;
    dropZone.style.backgroundSize = "cover";
    floatingMainTextFileInput.style.color = "white";
    floatingSubTextFileInput.style.color = "#e6e6e6";
}

if (dropZone.dataset.imagePath) {
    updateFileInput(dropZone.dataset.imagePath);
}

fileInput.addEventListener("change", () => updateFileInput());

dropZone.addEventListener("dragover", (event) => {
    event.preventDefault();
})

dropZone.addEventListener("drop", (event) => {
    event.preventDefault();
    fileInput.files = event.dataTransfer.files;
    updateFileInput();
});