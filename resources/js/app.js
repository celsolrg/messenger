async function checkAuth() {

    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {

        const res = await fetch('/api/me', {
            headers: {
                Authorization: 'Bearer ' + token
            }
        });

        if (!res.ok) {
            throw new Error();
        }

        document.body.style.display = 'block';

        await loadContacts();
        await loadCampaigns();

    } catch {

        localStorage.removeItem("token");
        window.location.href = "/login";
    }
}

document.addEventListener("DOMContentLoaded", checkAuth);
