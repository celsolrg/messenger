async function api(url, options = {}) {
    const token = localStorage.getItem("token");

    const res = await fetch("/api" + url, {
        ...options,
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token,
            ...(options.headers || {})
        }
    });

    if (!res.ok) {
        throw new Error("Erro API");
    }

    return res.json();
}
