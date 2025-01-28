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
    <nav class="py-4">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div>
                    <a href="index.php" class="text-xl font-medium">TechBlog</a>
                </div>
                <div class="flex items-center space-x-8">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="nuevo-post.php" class="text-gray-600 hover:text-gray-900">Nuevo Post</a>
                        <a href="logout.php" class="text-gray-600 hover:text-gray-900">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="register.php" class="text-gray-600 hover:text-gray-900">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Categories -->
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-center space-x-12">
                <a href="index.php" class="text-purple-600">all topics</a>
                <a href="index.php?categoria=programacion" class="text-gray-600 hover:text-gray-900">programación</a>
                <a href="index.php?categoria=hardware" class="text-gray-600 hover:text-gray-900">hardware</a>
                <a href="index.php?categoria=software" class="text-gray-600 hover:text-gray-900">software</a>
                <a href="index.php?categoria=redes" class="text-gray-600 hover:text-gray-900">redes</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="py-20">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-6xl font-serif text-center">All topics</h1>
        </div>
    </header>

    <!-- Posts Grid -->
    <main class="max-w-6xl mx-auto px-4 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
            <!-- Post 1 -->
            <article class="group">
                <a href="#" class="block aspect-[4/3] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3" 
                         alt="Programación"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                </a>
                <div class="mt-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-3">programación</p>
                    <h2 class="text-2xl font-serif mb-3">
                        <a href="#" class="hover:text-gray-600">Introducción a Python: El lenguaje más versátil</a>
                    </h2>
                    <p class="text-gray-600 mb-4">Descubre por qué Python se ha convertido en el lenguaje de programación más popular para principiantes y profesionales. Desde su sintaxis clara hasta sus múltiples aplicaciones...</p>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>Juan Pérez</span>
                        <span>15/01/2024</span>
                    </div>
                </div>
            </article>

            <!-- Post 2 -->
            <article class="group">
                <a href="#" class="block aspect-[4/3] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3" 
                         alt="Hardware"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                </a>
                <div class="mt-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-3">hardware</p>
                    <h2 class="text-2xl font-serif mb-3">
                        <a href="#" class="hover:text-gray-600">Guía definitiva para elegir componentes de PC</a>
                    </h2>
                    <p class="text-gray-600 mb-4">Todo lo que necesitas saber para construir tu PC ideal. Desde la selección de la CPU hasta la tarjeta gráfica, aprende a elegir los componentes perfectos para tus necesidades...</p>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>María García</span>
                        <span>18/01/2024</span>
                    </div>
                </div>
            </article>

            <!-- Post 3 -->
            <article class="group">
                <a href="#" class="block aspect-[4/3] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3" 
                         alt="Redes"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                </a>
                <div class="mt-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-3">redes</p>
                    <h2 class="text-2xl font-serif mb-3">
                        <a href="#" class="hover:text-gray-600">Seguridad en redes: Protege tu infraestructura</a>
                    </h2>
                    <p class="text-gray-600 mb-4">Aprende las mejores prácticas de seguridad en redes. Desde la configuración de firewalls hasta la implementación de VPNs, descubre cómo mantener tu red segura...</p>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>Carlos Rodríguez</span>
                        <span>20/01/2024</span>
                    </div>
                </div>
            </article>
        </div>
    </main>
</body>
</html>