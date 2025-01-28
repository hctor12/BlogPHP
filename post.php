<?php
require_once 'config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, u.nombre, u.apellidos 
    FROM posts p 
    JOIN usuarios u ON p.autor_id = u.id 
    WHERE p.id = :id
");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$puede_editar = isset($_SESSION['user_id']) && 
                ($_SESSION['user_id'] == $post['autor_id'] || $_SESSION['is_admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['titulo']) ?> - Blog de Informática</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center h-16">
                <div>
                    <a href="index.php" class="text-2xl font-serif font-bold text-gray-900">TechBlog</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="nuevo-post.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
                            Nuevo Post
                        </a>
                        <a href="logout.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
                            Login
                        </a>
                        <a href="register.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto py-12">
            <article class="bg-white">
                <header class="mb-8">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-3">
                        <?= htmlspecialchars($post['categoria']) ?>
                    </p>
                    <h1 class="text-4xl font-serif mb-4">
                        <?= htmlspecialchars($post['titulo']) ?>
                    </h1>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span class="font-serif"><?= htmlspecialchars($post['nombre'] . ' ' . $post['apellidos']) ?></span>
                        <span><?= date('d/m/Y', strtotime($post['fecha_publicacion'])) ?></span>
                    </div>
                </header>

                <div class="prose max-w-none font-serif text-gray-800 leading-relaxed">
                    <?= $post['contenido'] ?>
                </div>

                <?php if ($puede_editar): ?>
                <div class="mt-8 flex space-x-4">
                    <a href="editar-post.php?id=<?= $post['id'] ?>" 
                       class="bg-black text-white rounded-lg py-3 px-6 hover:bg-gray-800 transition-colors">
                        Editar
                    </a>
                    <form method="POST" action="eliminar-post.php" class="inline">
                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                        <button type="submit" 
                                onclick="return confirm('¿Estás seguro de eliminar este post?')"
                                class="bg-red-600 text-white rounded-lg py-3 px-6 hover:bg-red-700 transition-colors">
                            Eliminar
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900 font-serif">
                        ← Volver a la página principal
                    </a>
                </div>
            </article>
        </div>
    </main>
</body>
</html>