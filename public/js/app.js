async function checkAuth() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {
        const res = await fetch('/api/me', {
            headers: {
                Authorization: 'Bearer ' + token,
                Accept: 'application/json'
            }
        });

        if (!res.ok) {
            throw new Error();
        }

        document.body.style.display = 'block';

        await loadContacts();
        await loadCampaigns();

        updateDashboardCards();

    } catch {
        localStorage.removeItem("token");
        window.location.href = "/login";
    }
}

function updateDashboardCards() {
    const cardContacts = document.getElementById("cardContacts");
    const cardCampaigns = document.getElementById("cardCampaigns");
    const cardSent = document.getElementById("cardSent");
    const cardPending = document.getElementById("cardPending");

    if (cardContacts) cardContacts.innerText = contacts?.length ?? 0;
    if (cardCampaigns) cardCampaigns.innerText = campaigns?.length ?? 0;

    if (cardSent) cardSent.innerText = "-";
    if (cardPending) cardPending.innerText = "-";
}

function logout() {
    localStorage.removeItem("token");
    window.location.href = "/login";
}

document.addEventListener("DOMContentLoaded", checkAuth);
