function preserveGetFromPagination(event) {
    const button = event.target.closest("#pager button[data-page]");
    if (!button || button.disabled) return;

    const targetPage = button.dataset.page;
    if (!targetPage) return;

    const url = new URL(window.location.href);
    url.searchParams.set("page", targetPage);
    window.location.href = url.toString();
}

document.getElementById("pager").addEventListener("click", preserveGetFromPagination);