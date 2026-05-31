let contacts = [];

async function loadContacts() {
    contacts = await api("/contacts");
    renderContacts();
}

async function importContacts(csv) {
    const lines = csv.trim().split("\n");

    for (const line of lines) {
        const [phone, name] = line.split(",");

        if (!phone) continue;

        await api("/contacts", {
            method: "POST",
            body: JSON.stringify({
                name: name?.trim() || null,
                phone: phone.trim(),
                opt_in: true
            })
        });
    }

    await loadContacts();
}

async function deleteContact(id) {
    await api("/contacts/" + id, {
        method: "DELETE"
    });

    await loadContacts();
}
