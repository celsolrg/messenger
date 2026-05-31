<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-6 rounded shadow w-80">
    <h2 class="text-xl font-bold mb-4 text-center">Login</h2>

    <input id="login" type="text" placeholder="Usuário ou Email"
        class="w-full border p-2 mb-3 rounded">

    <input id="password" type="password" placeholder="Senha"
        class="w-full border p-2 mb-4 rounded">

    <button onclick="login()"
        class="w-full bg-green-500 text-white p-2 rounded">
        Entrar
    </button>

    <p id="error" class="text-red-500 text-sm mt-2 hidden"></p>
</div>

<script>
async function login() {
    const login = document.getElementById("login").value;
    const password = document.getElementById("password").value;

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

        if (!res.ok) {
            throw new Error(data.message || 'Login inválido');
        }

        localStorage.setItem("token", data.token);
        localStorage.setItem("user", JSON.stringify(data.user));

        window.location.href = "/";

    } catch (e) {
        const err = document.getElementById("error");
        err.innerText = e.message || "Login inválido";
        err.classList.remove("hidden");
    }
}
</script>

</body>
</html>
