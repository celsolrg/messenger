async function checkAuth() {
    const token = localStorage.getItem("token");

    console.log("TOKEN NO APP:", token);

    if (!token) {
        console.warn("Sem token, redirecionando login");
        console.warn("REDIRECT BLOQUEADO PARA DEBUG");
        return;
    }

    const res = await fetch('/api/me', {
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        }
    });

    console.log("STATUS /api/me:", res.status);

    if (res.status === 401) {
        console.error("Token inválido em /api/me");
        console.log(await res.text());
        return;
    }

    if (!res.ok) {
        console.error("Erro /api/me:", res.status, await res.text());
        return;
    }

    document.body.style.display = 'block';

    try {
        if (typeof loadContacts === "function") await loadContacts();
    } catch (e) {
        console.error("Erro loadContacts:", e);
    }

    try {
        if (typeof loadCampaigns === "function") await loadCampaigns();
    } catch (e) {
        console.error("Erro loadCampaigns:", e);
    }
}

document.addEventListener("DOMContentLoaded", checkAuth);
