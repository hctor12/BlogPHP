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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#contenido',
            plugins: 'link image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | code'
        });
    </script>
</head>
<body class="bg-white">
    <nav class="py-4">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <a href="index.php" class="text-xl font-medium">TechBlog</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-3xl font-serif mb-8">Editar Post</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="titulo" class="block text-base font-serif text-gray-900 mb-2">Título</label>
                    <input type="text" id="titulo" name="titulo" required 
                           value="<?= htmlspecialchars($post['titulo']) ?>"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                </div>

                <div>
                    <label for="categoria" class="block text-base font-serif text-gray-900 mb-2">Categoría</label>
                    <select id="categoria" name="categoria" required 
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                        <option value="programacion" <?= $post['categoria'] === 'programacion' ? 'selected' : '' ?>>Programación</option>
                        <option value="hardware" <?= $post['categoria'] === 'hardware' ? 'selected' : '' ?>>Hardware</option>
                        <option value="software" <?= $post['categoria'] === 'software' ? 'selected' : '' ?>>Software</option>
                        <option value="redes" <?= $post['categoria'] === 'redes' ? 'selected' : '' ?>>Redes</option>
                    </select>
                </div>

                <div>
                    <label for="contenido" class="block text-base font-serif text-gray-900 mb-2">Contenido</label>
                    <textarea id="contenido" name="contenido" rows="10" required 
                              class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-black focus:ring-black">
                        <?= htmlspecialchars($post['contenido']) ?>
                    </textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-black text-white rounded-lg py-3 px-6 hover:bg-gray-800 transition-colors">
                        Actualizar Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>