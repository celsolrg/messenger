async function api(url, options = {}) {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const res = await fetch("/api" + url, {
        ...options,
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token,
            ...(options.headers || {})
        }
    });

    if (res.status === 401) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        window.location.href = "/login";
        return;
    }

    if (!res.ok) {
        const text = await res.text();
        console.error("Erro API:", res.status, text);
        throw new Error("Erro API " + res.status);
    }

    return res.json();
}
