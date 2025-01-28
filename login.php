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
    <title>Login - Blog de Informática</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <div class="min-h-screen flex flex-col">
        <nav class="py-4">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex justify-between items-center">
                    <a href="index.php" class="text-xl font-medium">TechBlog</a>
                </div>
            </div>
        </nav>

        <div class="flex-grow flex items-center justify-center py-12 px-4">
            <div class="max-w-md w-full">
                <h2 class="text-3xl font-serif text-center mb-8">Iniciar Sesión</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

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
</body>
</html>