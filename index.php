<?php
require_once 'config/database.php';

$categoria = $_GET['categoria'] ?? null;
$titulo_pagina = $categoria ? ucfirst($categoria) : 'All topics';

if ($categoria) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.nombre, u.apellidos 
        FROM posts p 
        JOIN usuarios u ON p.autor_id = u.id 
        WHERE p.categoria = :categoria 
        ORDER BY p.fecha_publicacion DESC
    ");
    $stmt->execute(['categoria' => $categoria]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, u.nombre, u.apellidos 
        FROM posts p 
        JOIN usuarios u ON p.autor_id = u.id 
        ORDER BY p.fecha_publicacion DESC
    ");
    $stmt->execute();
}

$posts = $stmt->fetchAll();

// Función para obtener la imagen según la categoría
function getCategoryImage($categoria) {
    $images = [
        'programacion' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3',
        'hardware' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?ixlib=rb-4.0.3',
        'software' => 'https://images.unsplash.com/photo-1550439062-609e1531270e?ixlib=rb-4.0.3',
        'redes' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3'
    ];
    
    return $images[$categoria] ?? $images['programacion'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog de Informática</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
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

    <!-- Categories -->
    <div class="py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-center space-x-12">
                <a href="index.php" class="<?= !$categoria ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">all topics</a>
                <a href="index.php?categoria=programacion" class="<?= $categoria === 'programacion' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">programación</a>
                <a href="index.php?categoria=hardware" class="<?= $categoria === 'hardware' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">hardware</a>
                <a href="index.php?categoria=software" class="<?= $categoria === 'software' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">software</a>
                <a href="index.php?categoria=redes" class="<?= $categoria === 'redes' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">redes</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="py-20">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-6xl font-serif text-center"><?= htmlspecialchars($titulo_pagina) ?></h1>
        </div>
    </header>

    <!-- Posts Grid -->
    <main class="max-w-6xl mx-auto pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
            <?php foreach ($posts as $post): ?>
                <article class="group">
                    <a href="post.php?id=<?= $post['id'] ?>" class="block aspect-[4/3] overflow-hidden">
                        <img src="<?= getCategoryImage($post['categoria']) ?>" 
                             alt="<?= htmlspecialchars($post['titulo']) ?>"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </a>
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 uppercase tracking-wider mb-3"><?= htmlspecialchars($post['categoria']) ?></p>
                        <h2 class="text-2xl font-serif mb-3">
                            <a href="post.php?id=<?= $post['id'] ?>" class="hover:text-gray-600"><?= htmlspecialchars($post['titulo']) ?></a>
                        </h2>
                        <p class="text-gray-600 mb-4"><?= substr(strip_tags($post['contenido']), 0, 150) ?>...</p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><?= htmlspecialchars($post['nombre'] . ' ' . $post['apellidos']) ?></span>
                            <span><?= date('d/m/Y', strtotime($post['fecha_publicacion'])) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>