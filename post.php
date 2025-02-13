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

$puede_editar = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['autor_id'];
$puede_eliminar = isset($_SESSION['user_id']) &&
    ($_SESSION['user_id'] == $post['autor_id'] ||
        ($_SESSION['is_admin'] && $_SESSION['user_id'] != $post['autor_id']));
?>
<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['titulo']) ?> - Blog</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }

        .ql-container.ql-snow {
            border: none;
            font-family: ui-serif, Georgia, Cambria, Times New Roman, Times, serif;
            font-size: 1.125rem;
        }

        .ql-editor {
            padding: 0.5rem 0;
            max-height: none;
            overflow-y: visible;
        }

        .ql-editor.ql-blank::before {
            left: 0;
            font-style: normal;
            color: #d1d5db;
        }

        /* Estilos personalizados para el editor */
        .ql-snow .ql-picker.ql-size .ql-picker-label::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item::before {
            content: 'Normal';
            font-size: 13px !important;
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="10px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="10px"]::before {
            content: 'Pequeño';
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="13px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="13px"]::before {
            content: 'Normal';
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before {
            content: 'Mediano';
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="20px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="20px"]::before {
            content: 'Grande';
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before {
            content: 'Muy Grande';
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="32px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="32px"]::before {
            content: 'Enorme';
        }

        /* Estilos para el selector de fuentes */
        .ql-snow .ql-picker.ql-font .ql-picker-label::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item::before {
            content: 'Arial';
        }

        .ql-snow .ql-picker.ql-font.ql-expanded .ql-picker-options {
            min-width: 120px;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before {
            content: 'Arial';
            font-family: Arial, sans-serif;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before {
            content: 'Verdana';
            font-family: Verdana, sans-serif;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="tahoma"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="tahoma"]::before {
            content: 'Tahoma';
            font-family: Tahoma, sans-serif;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="trebuchet"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="trebuchet"]::before {
            content: 'Trebuchet MS';
            font-family: 'Trebuchet MS', sans-serif;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before {
            content: 'Georgia';
            font-family: Georgia, serif;
        }

        .ql-font-arial {
            font-family: Arial, sans-serif;
        }

        .ql-font-verdana {
            font-family: Verdana, sans-serif;
        }

        .ql-font-tahoma {
            font-family: Tahoma, sans-serif;
        }

        .ql-font-trebuchet {
            font-family: 'Trebuchet MS', sans-serif;
        }

        .ql-font-georgia {
            font-family: Georgia, serif;
        }

        /* Estilos para alineación de texto */
        .ql-align-center {
            text-align: center;
        }

        .ql-align-right {
            text-align: right;
        }

        .ql-align-justify {
            text-align: justify;
        }

        /* Estilos para listas */
        .prose ul,
        .prose ol,
        .ql-editor ul,
        .ql-editor ol {
            list-style-type: revert !important;
            margin: 1.25em 0 !important;
            padding-left: 1.625em !important;
        }

        .prose ul>li,
        .prose ol>li,
        .ql-editor ul>li,
        .ql-editor ol>li {
            margin: 0.5em 0 !important;
            padding-left: 0.375em !important;
        }

        .prose ul>li::before,
        .ql-editor ul>li::before {
            display: none !important;
        }

        .prose ol,
        .ql-editor ol {
            list-style-type: decimal !important;
        }

        .prose ul,
        .ql-editor ul {
            list-style-type: disc !important;
        }

        /* Estilos para bloques de código */
        .ql-editor pre.ql-syntax,
        .prose pre.ql-syntax {
            background-color: #23241f !important;
            color: #f8f8f2 !important;
            overflow: visible;
            padding: 1em !important;
            border-radius: 0.375rem !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            white-space: pre-wrap !important;
            margin: 1em 0 !important;
        }

        /* Ajustes para la vista de post */
        .ql-container.ql-snow {
            border: none;
        }

        .ql-editor {
            padding: 0;
        }

        .prose .ql-editor {
            all: inherit;
            font-family: inherit;
        }

        .prose .ql-editor>* {
            margin: revert;
            padding: revert;
        }

        /* Estilos adicionales para las listas */
        .prose .ql-editor ul li,
        .prose .ql-editor ol li {
            list-style: revert !important;
        }

        /* Añadir estilo para el contenedor principal */
        .min-h-screen-minus-header {
            min-height: calc(100vh - 96px);
        }
    </style>
</head>

<body class="min-h-full flex flex-col">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md">
        <div class="max-w-6xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div>
                    <a href="index.php" class="text-2xl font-serif font-bold text-gray-900">Blog</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="nuevo-post.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
                            Nuevo Post
                        </a>
                        <a href="logout.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
                            Iniciar Sesión
                        </a>
                        <a href="register.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Registrarte
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col bg-white">
        <main class="flex-grow pt-24 pb-12 md:pb-20">
            <div class="min-h-screen-minus-header max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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

                    <div class="prose max-w-none font-serif text-gray-800 leading-relaxed mb-8">
                        <?= $post['contenido'] ?>
                    </div>

                    <div class="mt-8 flex space-x-4">
                        <?php if ($puede_editar): ?>
                            <a href="editar-post.php?id=<?= $post['id'] ?>"
                                class="bg-black text-white rounded-lg py-3 px-6 hover:bg-gray-800 transition-colors">
                                Editar
                            </a>
                        <?php endif; ?>

                        <?php if ($puede_eliminar): ?>
                            <form id="deleteForm" method="POST" action="eliminar-post.php" class="inline">
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="button"
                                    onclick="showModal()"
                                    class="bg-red-600 text-white rounded-lg py-3 px-6 hover:bg-red-700 transition-colors">
                                    Eliminar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <a href="index.php" class="text-gray-600 hover:text-gray-900 font-serif">
                            ← Volver a la página principal
                        </a>
                    </div>
                </article>
            </div>
        </main>

        <!-- Modal de confirmación -->
        <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 transform transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-medium text-gray-900">Confirmar eliminación</h3>
                    </div>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <p id="confirmMessage" class="text-sm text-gray-500">¿Estás seguro de que deseas eliminar este post? Esta acción no se puede deshacer.</p>
                </div>
                <div class="mt-6 flex space-x-4">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-lg py-2 px-4 hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmDelete()" class="flex-1 bg-red-600 text-white rounded-lg py-2 px-4 hover:bg-red-700 transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-black text-white py-8 md:py-12">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 place-items-center text-center">
                    <div class="w-full">
                        <h3 class="text-xl font-serif mb-4">Blog</h3>
                        <p class="text-gray-400">Tu fuente de información sobre tecnología, programación y más.</p>
                    </div>
                    <div class="w-full">
                        <h3 class="text-xl font-serif mb-4">Categorías</h3>
                        <ul class="space-y-2">
                            <li><a href="index.php?categoria=programacion" class="text-gray-400 hover:text-white transition-colors">Programación</a></li>
                            <li><a href="index.php?categoria=hardware" class="text-gray-400 hover:text-white transition-colors">Hardware</a></li>
                            <li><a href="index.php?categoria=software" class="text-gray-400 hover:text-white transition-colors">Software</a></li>
                            <li><a href="index.php?categoria=redes" class="text-gray-400 hover:text-white transition-colors">Redes</a></li>
                        </ul>
                    </div>
                    <div class="w-full">
                        <h3 class="text-xl font-serif mb-4">Enlaces</h3>
                        <ul class="space-y-2">
                            <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Inicio</a></li>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><a href="nuevo-post.php" class="text-gray-400 hover:text-white transition-colors">Nuevo Post</a></li>
                                <li><a href="logout.php" class="text-gray-400 hover:text-white transition-colors">Cerrar Sesión</a></li>
                            <?php else: ?>
                                <li><a href="login.php" class="text-gray-400 hover:text-white transition-colors">Iniciar Sesión</a></li>
                                <li><a href="register.php" class="text-gray-400 hover:text-white transition-colors">Registrarte</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; <?= date('Y') ?> Blog. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function showModal() {
            document.getElementById('confirmModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmDelete() {
            document.getElementById('deleteForm').submit();
        }

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('confirmModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</body>

</html>