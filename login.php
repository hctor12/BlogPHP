<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['is_admin'] = $usuario['es_admin'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciales inválidas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white">
    <!-- Modal de error -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 transform transition-all">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Error</h3>
                </div>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-2">
                <p id="errorMessage" class="text-sm text-gray-500"></p>
            </div>
            <div class="mt-6">
                <button type="button" onclick="closeModal()" class="w-full bg-black text-white rounded-lg py-2 px-4 hover:bg-gray-800 transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md">
            <div class="max-w-6xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div>
                        <a href="index.php" class="text-2xl font-serif font-bold text-gray-900">Blog</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex-grow flex items-center justify-center py-12 px-4">
            <div class="max-w-md w-full">
                <h2 class="text-3xl font-serif text-center mb-8">Iniciar Sesión</h2>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-base font-serif text-gray-900 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label for="password" class="block text-base font-serif text-gray-900 mb-2">Contraseña</label>
                        <input type="password" id="password" name="password" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <button type="submit"
                        class="w-full bg-black text-white rounded-lg py-3 px-4 hover:bg-gray-800 transition-colors">
                        Iniciar Sesión
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    ¿No tienes cuenta?
                    <a href="register.php" class="text-black hover:text-gray-600">Regístrate</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function showModal(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('errorModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('errorModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // Mostrar error del servidor si existe
        <?php if ($error): ?>
            showModal(<?= json_encode($error) ?>);
        <?php endif; ?>
    </script>
</body>

</html>