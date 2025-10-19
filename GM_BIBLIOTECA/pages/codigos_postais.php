<?php
/**
 * Página de Gestão de Códigos Postais - Biblioteca Ginestal Machado
 * Texto em português de Portugal
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';
            
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $cod_postal = trim($_POST['cod_postal'] ?? '');
            $cod_localidade = trim($_POST['cod_localidade'] ?? '');

            // Validações
            if (empty($cod_postal) || empty($cod_localidade)) {
                throw new Exception("Código postal e localidade são obrigatórios!");
            }

            // Verificar se a tabela existe
            try {
                $teste = $conn->query("SELECT 1 FROM codigo_postal LIMIT 1");
            } catch (PDOException $e) {
                throw new Exception("A tabela 'codigo_postal' não existe ou não é acessível. Execute o instalador primeiro.");
            }

            // Verificar se já existe código postal
            $stmt = $conn->prepare("SELECT cod_postal FROM codigo_postal WHERE cod_postal = ?");
            $stmt->execute([$cod_postal]);
            if ($stmt->fetch()) {
                throw new Exception("Já existe um código postal com este número!");
            }

            $sql = "INSERT INTO codigo_postal (cod_postal, cod_localidade) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$cod_postal, $cod_localidade]);
            $mensagem = "Código postal adicionado com sucesso!";
            $tipo_mensagem = "success";
            
            // Limpar formulário após sucesso
            $_POST = [];
            
         } catch (PDOException $e) {
            $mensagem = "Erro ao adicionar código postal: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter lista de códigos postais
$codigos_postais = [];
try {
    $sql = "SELECT * FROM codigo_postal ORDER BY cod_localidade, cod_postal";
    $stmt = $conn->query($sql);
    $codigos_postais = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar códigos postais: " . $e->getMessage();
    $tipo_mensagem = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Códigos Postais - Biblioteca Ginestal Machado</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' fill='%23000'/><text x='50' y='70' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='%23B22222'>A</text><rect x='20' y='80' width='60' height='3' fill='%23FF8C00'/><rect x='25' y='85' width='50' height='2' fill='%23DC143C'/><rect x='30' y='90' width='40' height='2' fill='%23FF69B4'/></svg>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
        }

        .table-custom {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table-custom th {
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
        }

        .table-custom td {
            border-color: #f8f9fa;
            vertical-align: middle;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .badge-codigo {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-book-half"></i> Biblioteca Ginestal Machado
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="livros.php">
                            <i class="bi bi-book"></i> Livros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autores.php">
                            <i class="bi bi-person"></i> Autores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="utentes.php">
                            <i class="bi bi-people"></i> Utentes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requisicoes.php">
                            <i class="bi bi-arrow-left-right"></i> Requisições
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="editoras.php"><i class="bi bi-building"></i> Editoras</a></li>
                            <li><a class="dropdown-item" href="generos.php"><i class="bi bi-tags"></i> Géneros</a></li>
                            <li><a class="dropdown-item active" href="codigos_postais.php"><i class="bi bi-geo-alt"></i> Códigos Postais</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Mensagens -->
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensagem); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário de Adicionar Código Postal -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-geo-alt-fill"></i> Adicionar Novo Código Postal
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_postal" class="form-label">Código Postal *</label>
                        <input type="text" class="form-control" id="cod_postal" name="cod_postal" 
                               value="<?php echo htmlspecialchars($_POST['cod_postal'] ?? ''); ?>" 
                               placeholder="Ex: 1000-001" required>
                        <div class="form-text">Código postal da localidade</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="cod_localidade" class="form-label">Localidade *</label>
                        <input type="text" class="form-control" id="cod_localidade" name="cod_localidade" 
                               value="<?php echo htmlspecialchars($_POST['cod_localidade'] ?? ''); ?>" 
                               placeholder="Ex: Lisboa" required>
                        <div class="form-text">Nome da cidade ou localidade</div>
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Adicionar Código Postal
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-custom" onclick="limparFormulario()">
                        <i class="bi bi-arrow-clockwise"></i> Limpar
                    </button>
                    <a href="../index.php" class="btn btn-outline-secondary btn-custom">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Códigos Postais -->
        <?php if (!empty($codigos_postais)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-list-ul"></i> Códigos Postais Registados
                <span class="badge bg-primary"><?php echo count($codigos_postais); ?></span>
            </h2>
            
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Código Postal</th>
                            <th>Localidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codigos_postais as $codigo_postal): ?>
                            <?php
                            // Só mostra se ambos os campos existirem e não forem vazios
                            if (
                                !empty($codigo_postal['cod_postal']) &&
                                !empty($codigo_postal['cod_localidade'])
                            ):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($codigo_postal['cod_postal']); ?></td>
                                    <td><?php echo htmlspecialchars($codigo_postal['cod_localidade']); ?></td>
                                    <td>
                                        <a href="edit_codigo_postal.php?cod_postal=<?php echo urlencode($codigo_postal['cod_postal']); ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <a href="delete_codigo_postal.php?cod_postal=<?php echo urlencode($codigo_postal['cod_postal']); ?>" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="content-section text-center">
            <i class="bi bi-geo-alt" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhum código postal encontrado</h3>
            <p class="text-muted">Adicione o primeiro código postal à biblioteca.</p>
        </div>
        <?php endif; ?>

        <!-- Exemplos de Códigos Postais -->
        <div class="content-section">
            <h3 class="mb-3">
                <i class="bi bi-info-circle"></i> Exemplos de Códigos Postais Portugueses
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Principais Cidades:</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Lisboa</span>
                            <code>1000-001</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Porto</span>
                            <code>4000-001</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Braga</span>
                            <code>4700-001</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Aveiro</span>
                            <code>3800-001</code>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Estrutura:</h5>
                    <div class="alert alert-info">
                        <strong>Tabela:</strong> codigo_postal<br>
                        <strong>Campos:</strong><br>
                        • cod_postal = Código postal<br>
                        • cod_localidade = Nome da localidade<br>
                        <strong>Exemplo:</strong><br>
                        • Código: 1000-001<br>
                        • Localidade: Lisboa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Função para limpar o formulário
        function limparFormulario() {
            document.getElementById('cod_postal').value = '';
            document.getElementById('cod_localidade').value = '';
            document.getElementById('cod_postal').focus();
        }

        // Focar no campo código postal quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('cod_postal').focus();
        });
    </script>
</body>
</html>