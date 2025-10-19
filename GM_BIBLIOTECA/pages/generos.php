<?php
/**
 * Página de Gestão de Gêneros - Biblioteca Ginestal Machado
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de adição/edição/eliminação de gênero
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            
            // Validações
            if (empty($nome)) {
                throw new Exception("Nome do gênero é obrigatório!");
            }
            
            // Verificar se a tabela existe
            if (!$db->tabelaExiste('genero')) {
                throw new Exception("Tabela 'genero' não existe. Execute o instalador primeiro.");
            }
            
            // Verificar se já existe gênero com mesmo nome
            $stmt = $conn->prepare("SELECT ge_genero FROM genero WHERE ge_genero = ?");
            $stmt->execute([$nome]);
            if ($stmt->fetch()) {
                throw new Exception("Já existe um gênero com este nome!");
            }
            
            $sql = "INSERT INTO genero (ge_genero, descricao) VALUES (?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome, $descricao]);
            
            $mensagem = "Gênero '$nome' adicionado com sucesso!";
            $tipo_mensagem = "success";
            
            // Limpar formulário após sucesso
            $_POST = [];
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao adicionar gênero: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar edição de gênero
    elseif ($_POST['acao'] == 'editar') {
        try {
            $nome_antigo = $_POST['nome_antigo'] ?? '';
            $nome_novo = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            
            if (empty($nome_novo) || empty($nome_antigo)) {
                throw new Exception("Nome do gênero e nome antigo são obrigatórios!");
            }
            
            // Verificar se já existe outro gênero com o novo nome
            $stmt = $conn->prepare("SELECT ge_genero FROM genero WHERE ge_genero = ? AND ge_genero != ?");
            $stmt->execute([$nome_novo, $nome_antigo]);
            if ($stmt->fetch()) {
                throw new Exception("Já existe outro gênero com este nome!");
            }
            
            $sql = "UPDATE genero SET ge_genero = ?, descricao = ? WHERE ge_genero = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome_novo, $descricao, $nome_antigo]);
            
            $mensagem = "Género '$nome_antigo' actualizado para '$nome_novo' com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao actualizar género: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar eliminação de gênero
    elseif ($_POST['acao'] == 'eliminar') {
        try {
            $nome = $_POST['nome'] ?? '';
            
            if (empty($nome)) {
                throw new Exception("Nome do gênero é obrigatório!");
            }
            
            // Verificar se o gênero tem livros associados
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM livro WHERE li_genero = ?");
            $stmt->execute([$nome]);
            $total_livros = $stmt->fetch()['total'];
            
            if ($total_livros > 0) {
                throw new Exception("Não é possível eliminar este gênero pois tem $total_livros livro(s) associado(s).");
            }
            
            $sql = "DELETE FROM genero WHERE ge_genero = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome]);
            
            $mensagem = "Gênero '$nome' eliminado com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao eliminar gênero: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter lista de gêneros
$generos = [];
try {
    if ($db->tabelaExiste('genero')) {
        // Tentar primeiro com JOIN, se falhar, usar consulta simples
        try {
            $sql = "SELECT g.*, COUNT(l.li_cod) as total_livros 
                    FROM genero g 
                    LEFT JOIN livro l ON g.ge_genero = l.li_genero 
                    GROUP BY g.ge_genero 
                    ORDER BY g.ge_genero";
            $stmt = $conn->query($sql);
            $generos = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Se falhar, usar consulta simples sem JOIN
            $sql = "SELECT g.*, 0 as total_livros 
                    FROM genero g 
                    ORDER BY g.ge_genero";
            $stmt = $conn->query($sql);
            $generos = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar gêneros: " . $e->getMessage();
    $tipo_mensagem = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Géneros - Biblioteca Ginestal Machado</title>
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

        .genero-card {
            transition: transform 0.3s ease;
        }

        .genero-card:hover {
            transform: translateY(-5px);
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
                        <a class="nav-link dropdown-toggle active" href="#" id="gestaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="gestaoDropdown">
                            <li><a class="dropdown-item" href="editoras.php"><i class="bi bi-building"></i> Editoras</a></li>
                            <li><a class="dropdown-item active" href="generos.php"><i class="bi bi-tags"></i> Géneros</a></li>
                            <li><a class="dropdown-item" href="codigos_postais.php"><i class="bi bi-geo-alt"></i> Códigos Postais</a></li>
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

        <!-- Formulário de Adicionar Gênero -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-tags"></i> Adicionar Novo Gênero
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome do Gênero *</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                        <div class="form-text">Ex: Ficção, Romance, História</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                              placeholder="Descrição do gênero..."><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                    <div class="form-text">Breve descrição do tipo de livros que pertencem a este gênero</div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Adicionar Gênero
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

        <!-- Lista de Gêneros -->
        <?php if (!empty($generos)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-tags"></i> Gêneros Cadastrados
                <span class="badge bg-primary"><?php echo count($generos); ?></span>
            </h2>
            
            <div class="row">
                <?php foreach ($generos as $genero): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card genero-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($genero['ge_genero']); ?>
                            </h5>
                            
                            <p class="card-text">
                                <strong>Livros:</strong> 
                                <span class="badge bg-success"><?php echo $genero['total_livros']; ?></span>
                            </p>
                            
                            <?php if ($genero['descricao']): ?>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr($genero['descricao'], 0, 100)); ?>
                                        <?php if (strlen($genero['descricao']) > 100): ?>...<?php endif; ?>
                                    </small>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-outline-warning btn-sm" title="Editar" 
                                        onclick="editarGenero('<?php echo htmlspecialchars($genero['ge_genero'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($genero['descricao'] ?? '', ENT_QUOTES); ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" title="Eliminar" 
                                        onclick="eliminarGenero('<?php echo htmlspecialchars($genero['ge_genero'], ENT_QUOTES); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-outline-info btn-sm" title="Ver livros">
                                    <i class="bi bi-book"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="content-section text-center">
            <i class="bi bi-tags" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhum gênero encontrado</h3>
            <p class="text-muted">Adicione o primeiro gênero à biblioteca.</p>
        </div>
        <?php endif; ?>

        <!-- Exemplos de Gêneros -->
        <div class="content-section">
            <h3 class="mb-3">
                <i class="bi bi-info-circle"></i> Exemplos de Gêneros Literários
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Gêneros Ficcionais:</h5>
                    <ul class="list-group">
                        <li class="list-group-item">Ficção</li>
                        <li class="list-group-item">Romance</li>
                        <li class="list-group-item">Ficção Científica</li>
                        <li class="list-group-item">Fantasia</li>
                        <li class="list-group-item">Mistério</li>
                        <li class="list-group-item">Thriller</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Gêneros Não-Ficcionais:</h5>
                    <ul class="list-group">
                        <li class="list-group-item">Biografia</li>
                        <li class="list-group-item">História</li>
                        <li class="list-group-item">Ciências</li>
                        <li class="list-group-item">Filosofia</li>
                        <li class="list-group-item">Autoajuda</li>
                        <li class="list-group-item">Técnicos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Gênero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="nome_antigo" id="edit_nome_antigo">
                        
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome do Gênero *</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" rows="3" placeholder="Descrição do gênero..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Eliminação -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">Eliminar Gênero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="eliminar">
                        <input type="hidden" name="nome" id="delete_nome">
                        
                        <p>Tem certeza que deseja eliminar o gênero <strong id="delete_nome_display"></strong>?</p>
                        <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
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
            document.getElementById('nome').value = '';
            document.getElementById('descricao').value = '';
            document.getElementById('nome').focus();
        }

        // Função para editar gênero
        function editarGenero(nome, descricao = '') {
            document.getElementById('edit_nome_antigo').value = nome;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_descricao').value = descricao;
            
            var modal = new bootstrap.Modal(document.getElementById('modalEditar'));
            modal.show();
        }

        // Função para eliminar gênero
        function eliminarGenero(nome) {
            document.getElementById('delete_nome').value = nome;
            document.getElementById('delete_nome_display').textContent = nome;
            
            var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            modal.show();
        }

        // Focar no campo nome quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nome').focus();
        });
    </script>
</body>
</html>
