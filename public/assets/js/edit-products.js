function openEdit(link, id) {
    function navigateToEdit() {
        const url = new URL(window.location.href);
        url.pathname = new URL(link, url).pathname;

        url.searchParams.set("editProduct", id);
        editType.textContent = "Edit";
        window.location.href = url.toString();
    }
    
    const editType = document.getElementById("editType");
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has("editProduct")) {
        navigateToEdit();
        return;
    };

    const productId = urlParams.get("editProduct");
    if (productId != id) {
        navigateToEdit();
        return;
    }
    const dialog = document.querySelector("#add-product");
    editType.textContent = "Edit";
    if (!dialog.open) dialog.showModal();
}

function closeEdit() {
    const dialog = document.querySelector("#add-product");
    if (dialog.open) dialog.close();
    else clearEditState();
}

function clearEditState() {
    const url = new URL(window.location);
    if (url.searchParams.has("editProduct")) {
        url.searchParams.delete("editProduct");
        window.location.replace(url.toString());
        return;
    }

    document.getElementById("editType").textContent = "Add";
    document.getElementById("saveType").textContent = "Product";
}

function onLoad() {
    const dialog = document.querySelector("#add-product");
    dialog.addEventListener("close", clearEditState);
    if (dialog.dataset.editProduct === "true" && !dialog.open) {
        dialog.showModal();
    }
}

document.addEventListener("DOMContentLoaded", onLoad);