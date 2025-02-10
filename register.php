<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $admin_key = $_POST['admin_key'] ?? '';

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, introduce un email válido';
    }
    // Validar contraseña
    elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'La contraseña debe contener al menos un símbolo (!@#$%^&*(),.?":{}|<>)';
    } else {
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Este email ya está registrado';
        } else {
            $es_admin = ($admin_key === 'Administrador1') ? 1 : 0;

            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nombre, apellidos, email, password, es_admin) 
                VALUES (:nombre, :apellidos, :email, :password, :es_admin)
            ");

            try {
                $stmt->execute([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'es_admin' => $es_admin
                ]);

                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Error al registrar el usuario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Blog de Informática</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white">
    <!-- Modal de error -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 transform transition-all">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Error de validación</h3>
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
        <nav class="py-4">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex justify-between items-center">
                    <a href="index.php" class="text-xl font-medium">Blog</a>
                </div>
            </div>
        </nav>

        <div class="flex-grow flex items-center justify-center py-12 px-4">
            <div class="max-w-md w-full">
                <h2 class="text-3xl font-serif text-center mb-8">Registro</h2>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="nombre" class="block text-base font-serif text-gray-900 mb-2">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label for="apellidos" class="block text-base font-serif text-gray-900 mb-2">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label for="email" class="block text-base font-serif text-gray-900 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                            title="Por favor, introduce un email válido"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                        <p class="mt-1 text-sm text-gray-500">Ejemplo: usuario@dominio.com</p>
                    </div>

                    <div>
                        <label for="password" class="block text-base font-serif text-gray-900 mb-2">Contraseña</label>
                        <input type="password" id="password" name="password" required
                            minlength="8"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                        <p class="mt-1 text-sm text-gray-500">La contraseña debe tener al menos 8 caracteres y contener al menos un símbolo (!@#$%^&*(),.?":{}|<>)</p>
                    </div>

                    <div>
                        <label for="admin_key" class="block text-base font-serif text-gray-900 mb-2">
                            Clave de administrador (opcional)
                        </label>
                        <input type="password" id="admin_key" name="admin_key"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <button type="submit"
                        class="w-full bg-black text-white rounded-lg py-3 px-4 hover:bg-gray-800 transition-colors">
                        Registrarse
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    ¿Ya tienes cuenta?
                    <a href="login.php" class="text-black hover:text-gray-600">Inicia sesión</a>
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

        // Validación del lado del cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const email = document.getElementById('email').value;
            let error = '';

            // Validar email
            if (!email.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)) {
                error = 'Por favor, introduce un email válido';
            }
            // Validar contraseña
            else if (password.length < 8) {
                error = 'La contraseña debe tener al menos 8 caracteres';
            } else if (!password.match(/[!@#$%^&*(),.?":{}|<script>]/)) {
                error = 'La contraseña debe contener al menos un símbolo (!@#$%^&*(),.?":{}|<>)';
            }

            if (error) {
                e.preventDefault();
                showModal(error);
            }
        });
    </script>
</body>

</html>