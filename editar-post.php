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

if (!$post || ($post['autor_id'] != $_SESSION['user_id'] && !$_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    if (empty($titulo) || empty($contenido) || empty($categoria)) {
        $error = 'Todos los campos son obligatorios';
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
        } catch(PDOException $e) {
            $error = 'Error al actualizar el post';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post - Blog de Informática</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body class="bg-white h-screen flex flex-col">
    <nav class="bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center h-14">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="py-6">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-5xl font-serif text-center">Editar Post</h1>
        </div>
    </header>

    <main class="flex-1 max-w-3xl mx-auto w-full">
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

        <form method="POST" class="space-y-4" onsubmit="submitForm(event)">
            <div>
                <label for="titulo" class="block text-lg font-serif text-gray-900 mb-2">Título</label>
                <input type="text" id="titulo" name="titulo" required 
                       value="<?= htmlspecialchars($post['titulo']) ?>"
                       class="block w-full text-2xl font-serif border-0 border-b border-gray-200 focus:border-black focus:ring-0 pb-2 placeholder-gray-300"
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

            <div class="flex-1">
                <label for="editor" class="block text-lg font-serif text-gray-900 mb-2">Contenido</label>
                <div class="ql-toolbar-container">
                    <div id="editor" class="h-[calc(100vh-380px)] font-serif"></div>
                </div>
                <input type="hidden" name="contenido" id="contenido">
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-black text-white rounded-lg py-2.5 px-6 hover:bg-gray-800 transition-colors text-lg">
                    Actualizar Post
                </button>
            </div>
        </form>
    </main>

    <style>
        .ql-toolbar-container .ql-toolbar {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        .ql-container {
            border: none !important;
            font-family: ui-serif, Georgia, Cambria, Times New Roman, Times, serif;
            font-size: 1.125rem;
        }
        .ql-editor {
            padding: 0.5rem 0;
        }
        .ql-editor.ql-blank::before {
            left: 0;
            font-style: normal;
            color: #d1d5db;
        }
    </style>

    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Empieza a escribir tu post...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'header': [1, 2, false] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'code-block'],
                    ['clean']
                ]
            }
        });

        // Set initial content
        quill.root.innerHTML = <?= json_encode($post['contenido']) ?>;

        function submitForm(e) {
            e.preventDefault();
            var form = e.target;
            var contenido = document.querySelector('#contenido');
            contenido.value = quill.root.innerHTML;
            form.submit();
        }
    </script>
</body>
</html>