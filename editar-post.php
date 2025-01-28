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
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">Editar Post</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" id="titulo" name="titulo" required 
                           value="<?= htmlspecialchars($post['titulo']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <input type="text" id="categoria" name="categoria" required 
                           value="<?= htmlspecialchars($post['categoria']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="contenido" class="block text-sm font-medium text-gray-700">Contenido</label>
                    <textarea id="contenido" name="contenido" rows="10" required 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <?= htmlspecialchars($post['contenido']) ?>
                    </textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-blue-600 text-white rounded-md py-2 px-4 hover:bg-blue-700">
                        Actualizar Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>