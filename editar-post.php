<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

// Solo el autor puede editar su post
if (!$post || $post['autor_id'] != $_SESSION['user_id']) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    $contenido_limpio = trim(strip_tags($contenido));

    if (empty($titulo) || empty($contenido_limpio) || empty($categoria)) {
        $error = 'Todos los campos son obligatorios y el contenido no puede estar vacío';
    } else {
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET titulo = :titulo, contenido = :contenido, categoria = :categoria 
            WHERE id = :id
        ");

        try {
            $stmt->execute([
                'titulo' => $titulo,
                'contenido' => $contenido,
                'categoria' => $categoria,
                'id' => $id
            ]);

            $success = 'Post actualizado exitosamente';
            header('Location: post.php?id=' . $id);
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar el post';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post - Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>

<body class="bg-white flex flex-col min-h-screen">
    <div class="min-h-screen">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md">
            <div class="max-w-6xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div>
                        <a href="index.php" class="text-2xl font-serif font-bold text-gray-900">Blog</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="nuevo-post.php" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
                                Nuevo Post
                            </a>
                            <a href="logout.php" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Cerrar Sesión
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <div class="pt-24">
            <header class="py-4 sm:py-6">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl sm:text-5xl font-serif text-center">Editar Post</h1>
                </div>
            </header>

            <main class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex flex-col pb-12 md:pb-20">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-2 rounded mb-4">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4 flex-1 flex flex-col" onsubmit="return validateForm(event)">
                    <div>
                        <label for="titulo" class="block text-lg font-serif text-gray-900 mb-2">Título</label>
                        <input type="text" id="titulo" name="titulo" required
                            value="<?= htmlspecialchars($post['titulo']) ?>"
                            class="block w-full text-xl sm:text-2xl font-serif border-0 border-b border-gray-200 focus:border-black focus:ring-0 pb-2 placeholder-gray-300"
                            placeholder="Escribe el título de tu post...">
                    </div>

                    <div>
                        <label for="categoria" class="block text-lg font-serif text-gray-900 mb-2">Categoría</label>
                        <select id="categoria" name="categoria" required
                            class="block w-full border-0 border-b border-gray-200 focus:border-black focus:ring-0 pb-2 font-serif">
                            <option value="programacion" <?= $post['categoria'] === 'programacion' ? 'selected' : '' ?>>Programación</option>
                            <option value="hardware" <?= $post['categoria'] === 'hardware' ? 'selected' : '' ?>>Hardware</option>
                            <option value="software" <?= $post['categoria'] === 'software' ? 'selected' : '' ?>>Software</option>
                            <option value="redes" <?= $post['categoria'] === 'redes' ? 'selected' : '' ?>>Redes</option>
                        </select>
                    </div>

                    <div class="flex-1 flex flex-col">
                        <label for="editor" class="block text-lg font-serif text-gray-900 mb-2">Contenido</label>
                        <div id="editor" class="flex-1 font-serif min-h-24"></div>
                        <input type="hidden" name="contenido" id="contenido">
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <a href="javascript:history.back()"
                            class="bg-gray-100 text-gray-700 rounded-lg py-2 px-4 sm:py-2.5 sm:px-6 hover:bg-gray-200 transition-colors text-base sm:text-lg">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-black text-white rounded-lg py-2 px-4 sm:py-2.5 sm:px-6 hover:bg-gray-800 transition-colors text-base sm:text-lg">
                            Actualizar Post
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

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
            max-height: calc(100vh - 300px);
            overflow-y: auto;
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
    </style>

    <script>
        // Funciones para mostrar y cerrar el modal de error
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

        // Registrar el módulo de fuentes
        var Font = Quill.import('formats/font');
        Font.whitelist = ['arial', 'verdana', 'tahoma', 'trebuchet', 'georgia'];
        Quill.register(Font, true);

        // Registrar tamaños personalizados
        var Size = Quill.import('attributors/style/size');
        Size.whitelist = ['10px', '13px', '16px', '20px', '24px', '32px'];
        Quill.register(Size, true);

        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Empieza a escribir tu post...',
            modules: {
                toolbar: [
                    [{
                        'font': ['arial', 'verdana', 'tahoma', 'trebuchet', 'georgia']
                    }],
                    [{
                        'size': ['10px', '13px', '16px', '20px', '24px', '32px']
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        'align': []
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        quill.root.innerHTML = <?= json_encode($post['contenido']) ?>;

        function validateForm(e) {
            e.preventDefault();
            var form = e.target;
            var contenido = document.querySelector('#contenido');
            var contenidoQuill = quill.root.innerHTML;

            var contenidoLimpio = contenidoQuill.replace(/<[^>]*>/g, '').trim();

            if (!contenidoLimpio) {
                showModal('El contenido del post no puede estar vacío');
                return false;
            }

            contenido.value = contenidoQuill;
            form.submit();
        }
    </script>
</body>

</html>