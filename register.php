<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $admin_key = $_POST['admin_key'] ?? '';

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
        } catch(PDOException $e) {
            $error = 'Error al registrar el usuario';
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
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label for="password" class="block text-base font-serif text-gray-900 mb-2">Contraseña</label>
                        <input type="password" id="password" name="password" required 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
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
</body>
</html>