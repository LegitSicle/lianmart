function preserveGetFromSort() {
    const url = new URL(window.location.href);
    const searchInput = document.getElementById("searchInput").value;
    url.searchParams.set("sort", this.value);
    if (url.searchParams.has("sort")) {
        url.searchParams.set("page", "1");
    }
    if (url.searchParams.has("search") && searchInput === "") {
        url.searchParams.delete("search");
    }
    window.location.href = url.toString();
}

function preserveGetFromFilter(event) {
    event.preventDefault();
    const form = this;
    const url = new URL(window.location.href);

    const ownedKeys = new Set([...form.elements].map(el => el.name).filter(Boolean));
    ownedKeys.forEach(key => url.searchParams.delete(key));

    const groups = new Map();
    for (const [key, value] of new FormData(form)) {
        const v = value.trim();
        if (v === "") continue;
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(v);
    }

    for (const [key, values] of groups) {
        const field = form.elements[key];
        const el = field instanceof RadioNodeList ? field[0] : field;

        if (el.dataset && el.dataset.default !== undefined && values[0] === el.dataset.default) continue;

        if (key === "avail" && values.length === 2) continue;

        url.searchParams.set(key, values.join(","));
    }

    const searchInput = document.getElementById("searchInput").value;
    if (url.searchParams.has("search") && searchInput === "") {
        url.searchParams.delete("search");
    }
    url.searchParams.delete("page");

    window.location.href = url.toString();
}

document.getElementById("sort").addEventListener("change", preserveGetFromSort);
document.getElementById("filterForm").addEventListener("submit", preserveGetFromFilter);