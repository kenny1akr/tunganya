<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion | FursaPlus</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configuration Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fursa: {
                            green: '#36c783',
                            dark: '#287957',
                            background: '#f0f3f9'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-fursa-background flex flex-col
             items-center justify-center px-4 py-8
             font-sans text-gray-700">

    <!-- Conteneur principal -->
    <main class="w-full max-w-sm">

        <!-- Carte de connexion -->
        <section class="bg-white rounded-3xl shadow-lg
                        px-7 sm:px-8 py-8">

            <!-- Logo et nom -->
            <div class="text-center mb-5">

                <div class="flex items-center justify-center gap-2
                            text-fursa-dark">

                    <!-- Icône menu -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-7 h-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-linecap="round"
                    >
                        <path d="M4 6h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 18h16"></path>
                    </svg>

                    <h1 class="text-2xl font-bold tracking-tight">
                        FursaPlus
                    </h1>
                </div>

                <p class="text-sm text-gray-500 mt-4">
                    Accédez à votre compte CEPBU
                </p>
            </div>

            <!-- Message d'erreur ou de succès -->
            <div
                id="message"
                class="hidden mb-4 rounded-lg p-3 text-sm"
                role="status"
                aria-live="polite"
            ></div>

            <!-- Formulaire -->
            <form id="loginForm" novalidate>

                <!-- Identifiant -->
                <div class="mb-4">
                    <label
                        for="identifier"
                        class="block text-sm font-medium mb-2"
                    >
                        Identifiant ou Email
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        autocomplete="username"
                        required
                        class="w-full h-10 border border-gray-300
                               rounded-sm px-3 text-sm
                               outline-none transition
                               focus:border-fursa-green
                               focus:ring-2 focus:ring-fursa-green/20"
                    >
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label
                        for="password"
                        class="block text-sm font-medium mb-2"
                    >
                        Mot de passe
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="w-full h-10 border border-gray-300
                                   rounded-sm pl-3 pr-10 text-sm
                                   outline-none transition
                                   focus:border-fursa-green
                                   focus:ring-2 focus:ring-fursa-green/20"
                        >

                        <!-- Bouton afficher / masquer -->
                        <button
                            type="button"
                            id="togglePassword"
                            aria-label="Afficher le mot de passe"
                            aria-pressed="false"
                            class="absolute inset-y-0 right-0 px-3
                                   flex items-center text-gray-500
                                   hover:text-fursa-dark"
                        >
                            <svg
                                id="eyeIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M2 12s3.5-7 10-7
                                         10 7 10 7-3.5 7-10 7
                                         -10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex items-start justify-between
                            gap-3 mb-7 text-xs">

                    <!-- Se souvenir de moi -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-3.5 h-3.5 accent-fursa-green
                                   rounded cursor-pointer"
                        >

                        <span>Se souvenir de moi</span>
                    </label>

                    <!-- Mot de passe oublié -->
                    <a
                        href="forgot-password.html"
                        class="flex items-start gap-1 text-fursa-green
                               hover:text-fursa-dark font-medium"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-3.5 h-3.5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <path
                                d="M12 7v5m0 4h.01"
                                stroke="white"
                                stroke-width="2"
                                stroke-linecap="round"
                            ></path>
                        </svg>

                        <span>
                            Mot de passe oublié ?
                        </span>
                    </a>
                </div>

                <!-- Bouton de connexion -->
                <button
                    type="submit"
                    id="loginButton"
                    class="w-full h-11 rounded-md
                           bg-fursa-green text-white
                           font-semibold text-sm
                           hover:bg-fursa-dark
                           focus:outline-none focus:ring-4
                           focus:ring-fursa-green/25
                           transition duration-200
                           disabled:opacity-60"
                >
                    Se connecter
                </button>

            </form>
        </section>

        <!-- Description -->
        <p class="text-center text-[10px] text-gray-500 mt-8 leading-5">
            Solution intégrée de gestion :
            Projets | RH | Parc auto | Archivage
        </p>

        <!-- Copyright -->
        <p class="text-center text-[10px] text-gray-500 mt-5">
            &copy; <span id="year"></span> FursaPlus
        </p>

    </main>

    <!-- JavaScript -->
    <script>
        // Année automatique
        document.getElementById('year').textContent =
            new Date().getFullYear();

        const loginForm = document.getElementById('loginForm');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const message = document.getElementById('message');
        const loginButton = document.getElementById('loginButton');

        // Afficher ou masquer le mot de passe
        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';

            togglePassword.setAttribute(
                'aria-label',
                isHidden
                    ? 'Masquer le mot de passe'
                    : 'Afficher le mot de passe'
            );

            togglePassword.setAttribute(
                'aria-pressed',
                String(isHidden)
            );

            eyeIcon.innerHTML = isHidden
                ? `
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 002.8 2.8"></path>
                    <path d="M9.9 5.2A10.8 10.8 0 0112 5
                    c6.5 0 10 7 10 7a15 15 0 01-4 4.8"></path>
                    <path d="M6.2 6.2C3.5 8 2 12 2 12
                    s3.5 7 10 7a10 10 0 004-.8"></path>
                  `
                : `
                    <path d="M2 12s3.5-7 10-7
                    10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  `;
        });

        // Afficher un message
        function showMessage(text, type = 'error') {
            message.textContent = text;
            message.className =
                'mb-4 rounded-lg p-3 text-sm ' +
                (type === 'success'
                    ? 'bg-green-50 text-green-700 border border-green-200'
                    : 'bg-red-50 text-red-700 border border-red-200');
        }

        // Validation du formulaire
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const identifier =
                document.getElementById('identifier').value.trim();

            const password = passwordInput.value;

            if (!identifier || !password) {
                showMessage(
                    'Veuillez remplir tous les champs obligatoires.'
                );
                return;
            }

            if (password.length < 6) {
                showMessage(
                    'Le mot de passe doit contenir au moins 6 caractères.'
                );
                return;
            }

            /*
             * Démonstration uniquement :
             * aucune authentification réelle n'est effectuée ici.
             *
             * Remplace cette partie par un appel à ton backend PHP.
             */

            showMessage(
                'Les champs sont valides. Connectez ce formulaire à votre serveur.',
                'success'
            );
        });
    </script>

</body>
</html>