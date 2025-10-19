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
    $novo_cod_postal = $_POST['cod_postal'] ?? '';
    $cod_localidade = $_POST['cod_localidade'] ?? '';
    $cod_postal_original = $_POST['cod_postal_original'] ?? '';

    $stmt = $conn->prepare("UPDATE codigo_postal SET cod_postal=?, cod_localidade=? WHERE cod_postal=?");
    $stmt->execute([$novo_cod_postal, $cod_localidade, $cod_postal_original]);
    header('Location: codigos_postais.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM codigo_postal WHERE cod_postal=?");
$stmt->execute([$cod_postal]);
$codigo_postal = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Código Postal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title mb-3">Editar Código Postal</h4>
                <?php if ($codigo_postal): ?>
                    <form method="post">
                        <input type="hidden" name="cod_postal_original" value="<?php echo htmlspecialchars($codigo_postal['cod_postal']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="cod_postal" class="form-control" value="<?php echo htmlspecialchars($codigo_postal['cod_postal']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Localidade</label>
                            <input type="text" name="cod_localidade" class="form-control" value="<?php echo htmlspecialchars($codigo_postal['cod_localidade']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar
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