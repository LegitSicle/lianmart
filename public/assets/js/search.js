function preserveGetFromSearch(event) {
    event.preventDefault();

    const searchInput = document.getElementById("searchInput").value;
    const url = new URL(window.location.href);
    console.log(searchInput);

    if (searchInput.trim() !== "") {
        url.searchParams.set("search", searchInput);
        console.log(url.toString());
    } else {
        url.searchParams.delete("search");
    }

    if (url.searchParams.has("page")) {
        url.searchParams.set("page", "1");
    }
    window.location.href = url.toString();
}

document.getElementById("searchForm").addEventListener("submit", preserveGetFromSearch);
