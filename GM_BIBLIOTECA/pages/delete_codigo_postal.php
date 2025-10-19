<?php
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$cod_postal = $_GET['cod_postal'] ?? null;

if (!$cod_postal) {
    header('Location: codigos_postais.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $stmt = $conn->prepare("DELETE FROM codigo_postal WHERE cod_postal=?");
        $stmt->execute([$cod_postal]);
    }
    header('Location: codigos_postais.php');
    exit;
}

// Buscar dados do código postal para mostrar na confirmação
$stmt = $conn->prepare("SELECT * FROM codigo_postal WHERE cod_postal=?");
$stmt->execute([$cod_postal]);
$codigo_postal = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Código Postal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title mb-3">Eliminar Código Postal</h4>
                <?php if ($codigo_postal): ?>
                    <p>Tem certeza que deseja eliminar o código postal <strong><?php echo htmlspecialchars($codigo_postal['cod_postal']); ?></strong> - <?php echo htmlspecialchars($codigo_postal['cod_localidade']); ?>?</p>
                    <form method="post">
                        <button type="submit" name="confirm" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Confirmar
                        </button>
                        <a href="codigos_postais.php" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                    </form>
                <?php else: ?>
                    <p class="text-danger">Código postal não encontrado.</p>
                    <a href="codigos_postais.php" class="btn btn-secondary">Voltar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>