<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

<form id="loginForm" class="bg-white p-6 rounded shadow w-80">
    <h2 class="text-xl font-bold mb-4 text-center">Login</h2>

    <input id="login" name="login" type="text" placeholder="Usuário ou Email"
        autocomplete="username"
        class="w-full border p-2 mb-3 rounded">

    <input id="password" name="password" type="password" placeholder="Senha"
        autocomplete="current-password"
        class="w-full border p-2 mb-4 rounded">

    <button type="submit"
        class="w-full bg-green-500 text-white p-2 rounded">
        Entrar
    </button>

    <p id="error" class="text-red-500 text-sm mt-2 hidden"></p>
</form>

<script>
document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const login = document.getElementById("login").value.trim();
    const password = document.getElementById("password").value;

    const err = document.getElementById("error");
    err.classList.add("hidden");
    err.innerText = "";

    try {
        const res = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ login, password })
        });

        const data = await res.json();

        console.log("LOGIN STATUS:", res.status);
        console.log("LOGIN DATA:", data);

        if (!res.ok) {
            throw new Error(data.message || 'Login inválido');
        }

        if (!data.token) {
            throw new Error("API não retornou token");
        }

        localStorage.setItem("token", data.token);
        localStorage.setItem("user", JSON.stringify(data.user || {}));

        console.log("TOKEN SALVO:", localStorage.getItem("token"));

        window.location.assign("/");

    } catch (e) {
        console.error("ERRO LOGIN:", e);

        err.innerText = e.message || "Login inválido";
        err.classList.remove("hidden");
    }
});
</script>

</body>
</html>
