<?php
require_once 'config/database.php';

$categoria = $_GET['categoria'] ?? null;
$categorias = [
    'programacion' => 'Programación',
    'hardware' => 'Hardware',
    'software' => 'Software',
    'redes' => 'Redes'
];

$titulo_pagina = $categoria ? ($categorias[$categoria] ?? ucfirst($categoria)) : 'All topics';

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
<body class="bg-white min-h-screen flex flex-col">
    <div class="min-h-screen">
    <!-- Navbar - Now fixed and with blur effect -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md">
        <div class="max-w-6xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div>
                    <a href="index.php" class="text-2xl font-serif font-bold text-gray-900">Blog</a>
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

    <!-- Add padding to account for fixed navbar -->
    <div class="pt-24">
        <!-- Categories -->
        <div class="py-6 overflow-x-auto">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex justify-start md:justify-center space-x-6 md:space-x-12 min-w-max">
                    <a href="index.php" class="<?= !$categoria ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">all topics</a>
                    <a href="index.php?categoria=programacion" class="<?= $categoria === 'programacion' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">programación</a>
                    <a href="index.php?categoria=hardware" class="<?= $categoria === 'hardware' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">hardware</a>
                    <a href="index.php?categoria=software" class="<?= $categoria === 'software' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">software</a>
                    <a href="index.php?categoria=redes" class="<?= $categoria === 'redes' ? 'text-purple-600' : 'text-gray-600 hover:text-gray-900' ?>">redes</a>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="pb-12 md:pb-20 px-4 pt-4 md:pt-8">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-4xl md:text-6xl font-serif text-center"><?= htmlspecialchars($titulo_pagina) ?></h1>
            </div>
        </header>

        <!-- Posts Grid -->
        <main class="max-w-6xl mx-auto pb-12 md:pb-20 px-4 flex-grow">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16">
                <?php foreach ($posts as $post): ?>
                    <article class="group">
                        <a href="post.php?id=<?= $post['id'] ?>" class="block aspect-[4/3] overflow-hidden">
                            <img src="<?= getCategoryImage($post['categoria']) ?>" 
                                 alt="<?= htmlspecialchars($post['titulo']) ?>"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </a>
                        <div class="mt-4 md:mt-6">
                            <p class="text-sm text-gray-500 uppercase tracking-wider mb-2 md:mb-3"><?= htmlspecialchars($post['categoria']) ?></p>
                            <h2 class="text-xl md:text-2xl font-serif mb-2 md:mb-3">
                                <a href="post.php?id=<?= $post['id'] ?>" class="hover:text-gray-600"><?= htmlspecialchars($post['titulo']) ?></a>
                            </h2>
                            <p class="text-gray-600 mb-3 md:mb-4"><?= substr(strip_tags($post['contenido']), 0, 150) ?>...</p>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span><?= htmlspecialchars($post['nombre'] . ' ' . $post['apellidos']) ?></span>
                                <span><?= date('d/m/Y', strtotime($post['fecha_publicacion'])) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-white py-8 md:py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-serif mb-4">Blog</h3>
                    <p class="text-gray-400">Tu fuente de información sobre tecnología, programación y más.</p>
                </div>
                <div>
                    <h3 class="text-xl font-serif mb-4">Categorías</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php?categoria=programacion" class="text-gray-400 hover:text-white transition-colors">Programación</a></li>
                        <li><a href="index.php?categoria=hardware" class="text-gray-400 hover:text-white transition-colors">Hardware</a></li>
                        <li><a href="index.php?categoria=software" class="text-gray-400 hover:text-white transition-colors">Software</a></li>
                        <li><a href="index.php?categoria=redes" class="text-gray-400 hover:text-white transition-colors">Redes</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-serif mb-4">Enlaces</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Inicio</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="nuevo-post.php" class="text-gray-400 hover:text-white transition-colors">Nuevo Post</a></li>
                            <li><a href="logout.php" class="text-gray-400 hover:text-white transition-colors">Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-gray-400 hover:text-white transition-colors">Login</a></li>
                            <li><a href="register.php" class="text-gray-400 hover:text-white transition-colors">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> Blog. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>