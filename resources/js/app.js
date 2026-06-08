async function checkAuth() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {
        const res = await fetch('/api/me', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        if (res.status === 401) {
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            window.location.href = "/login";
            return;
        }

        if (!res.ok) {
            console.error("Erro ao validar /api/me:", res.status, await res.text());
            return;
        }

        document.body.style.display = 'block';

    } catch (e) {
        console.error("Falha no checkAuth:", e);
        return;
    }

    try {
        if (typeof loadContacts === "function") {
            await loadContacts();
        }
    } catch (e) {
        console.error("Erro em loadContacts:", e);
    }

    try {
        if (typeof loadCampaigns === "function") {
            await loadCampaigns();
        }
    } catch (e) {
        console.error("Erro em loadCampaigns:", e);
    }
}

document.addEventListener("DOMContentLoaded", checkAuth);
