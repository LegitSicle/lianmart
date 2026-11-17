function preserveGetFromSearch(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const url = new URL(window.location.href);

    for (const [key, value] of formData.entries()) {
        if (value.trim() !== "") {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    }

    if (url.searchParams.has("page")) {
        url.searchParams.set("page", "1");
    }

    window.location.href = url.toString();
}

document.getElementById("searchForm").addEventListener("submit", preserveGetFromSearch);
