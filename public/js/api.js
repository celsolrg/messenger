async function api(url, options = {}) {
    const token = localStorage.getItem("token");
    const isFormData = options.body instanceof FormData;

    const headers = {
        "Accept": "application/json",
        "Authorization": "Bearer " + token,
        ...(isFormData ? {} : { "Content-Type": "application/json" }),
        ...(options.headers || {})
    };

    const res = await fetch("/api" + url, {
        ...options,
        headers
    });

    const text = await res.text();

    console.log("API:", url, res.status, text);

    if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/login";
        return;
    }

    if (!res.ok) {
        throw new Error("Erro API " + res.status + ": " + text);
    }

    if (!text) {
        return null;
    }

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("Resposta não é JSON:", text);
        throw new Error("A API retornou HTML ou texto inválido.");
    }
}
