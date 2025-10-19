<?php
/**
 * Página de Gestão de Autores - Biblioteca Ginestal Machado
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de adição de autor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $biografia = trim($_POST['biografia'] ?? '');
            $data_nascimento = $_POST['data_nascimento'] ?? '';
            $nacionalidade = trim($_POST['nacionalidade'] ?? '');
            
            // Validações
            if (empty($nome)) {
                throw new Exception("Nome do autor é obrigatório!");
            }
            
            // Verificar se a tabela autor existe
            if (!$db->tabelaExiste('autor')) {
                throw new Exception("Tabela 'autor' não existe. Execute o instalador primeiro.");
            }
            
            // Verificar se já existe autor com mesmo nome
            $stmt = $conn->prepare("SELECT au_cod FROM autor WHERE au_nome = ?");
            $stmt->execute([$nome]);
            if ($stmt->fetch()) {
                throw new Exception("Já existe um autor com este nome!");
            }
            
            // Preparar dados para inserção
            $data_nascimento = !empty($data_nascimento) ? $data_nascimento : null;
            
            $sql = "INSERT INTO autor (au_nome, au_pais, data_nascimento, biografia, nacionalidade) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome, $nacionalidade, $data_nascimento, $biografia, $nacionalidade]);
            
            $mensagem = "Autor '$nome' adicionado com sucesso!";
            $tipo_mensagem = "success";
            
            // Limpar formulário após sucesso
            $_POST = [];
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao adicionar autor: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Processar edição de autor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    try {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $nacionalidade = trim($_POST['nacionalidade']);
        $data_nascimento = $_POST['data_nascimento'];
        $biografia = trim($_POST['biografia']);
        
        if (empty($nome)) {
            throw new Exception("Nome do autor é obrigatório!");
        }
        
        // Preparar dados para atualização
        $data_nascimento = !empty($data_nascimento) ? $data_nascimento : null;
        
        $sql = "UPDATE autor SET au_nome = ?, au_pais = ?, data_nascimento = ?, biografia = ?, nacionalidade = ? WHERE au_cod = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $nacionalidade, $data_nascimento, $biografia, $nacionalidade, $id]);
        
        $mensagem = "Autor '$nome' editado com sucesso!";
        $tipo_mensagem = "success";
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao editar autor: " . $e->getMessage();
        $tipo_mensagem = "danger";
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Processar eliminação de autor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'eliminar') {
    try {
        $id = $_POST['id'];
        
        // Verificar se o autor tem livros associados
        if ($db->tabelaExiste('livro')) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM livro WHERE li_autor = ?");
            $stmt->execute([$id]);
            $total_livros = $stmt->fetch()['total'];
            
            if ($total_livros > 0) {
                throw new Exception("Não é possível eliminar este autor pois tem $total_livros livro(s) associado(s).");
            }
        }
        
        $sql = "DELETE FROM autor WHERE au_cod = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        $mensagem = "Autor eliminado com sucesso!";
        $tipo_mensagem = "success";
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao eliminar autor: " . $e->getMessage();
        $tipo_mensagem = "danger";
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Obter lista de autores
$autores = [];
try {
    if ($db->tabelaExiste('autor')) {
        // Tentar primeiro com JOIN, se falhar, usar consulta simples
        try {
            $sql = "SELECT a.*, COUNT(l.li_cod) as total_livros 
                    FROM autor a 
                    LEFT JOIN livro l ON a.au_cod = l.li_autor 
                    GROUP BY a.au_cod 
                    ORDER BY a.au_nome";
            $stmt = $conn->query($sql);
            $autores = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Se falhar, usar consulta simples sem JOIN
            $sql = "SELECT a.*, 0 as total_livros 
                    FROM autor a 
                    ORDER BY a.au_nome";
            $stmt = $conn->query($sql);
            $autores = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar autores: " . $e->getMessage();
    $tipo_mensagem = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Autores - Biblioteca Ginestal Machado</title>
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-book-half"></i> Biblioteca Ginestal Machado
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link active" href="autores.php">
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
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="editoras.php"><i class="bi bi-building"></i> Editoras</a></li>
                            <li><a class="dropdown-item" href="generos.php"><i class="bi bi-tags"></i> Géneros</a></li>
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

        <!-- Formulário de Adicionar Autor -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-person-plus"></i> Adicionar Novo Autor
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome do Autor *</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                        <div class="form-text">Nome completo do autor</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nacionalidade" class="form-label">Nacionalidade</label>
                        <input type="text" class="form-control" id="nacionalidade" name="nacionalidade" 
                               value="<?php echo htmlspecialchars($_POST['nacionalidade'] ?? ''); ?>" 
                               placeholder="Ex: Portuguesa">
                        <div class="form-text">Ex: Portuguesa, Brasileira, Espanhola</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                               value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>">
                        <div class="form-text">Opcional - Data de nascimento do autor</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="biografia" class="form-label">Biografia</label>
                    <textarea class="form-control" id="biografia" name="biografia" rows="4" 
                              placeholder="Breve biografia do autor..."><?php echo htmlspecialchars($_POST['biografia'] ?? ''); ?></textarea>
                    <div class="form-text">Informações sobre a vida e obra do autor (opcional)</div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-person-plus"></i> Adicionar Autor
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

        <!-- Lista de Autores -->
        <?php if (!empty($autores)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-people"></i> Autores Cadastrados
                <span class="badge bg-primary"><?php echo count($autores); ?></span>
            </h2>
            
            <div class="row">
                <?php foreach ($autores as $autor): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($autor['au_nome']); ?>
                            </h5>
                            
                            <?php if ($autor['nacionalidade']): ?>
                                <p class="card-text">
                                    <strong>Nacionalidade:</strong> <?php echo htmlspecialchars($autor['nacionalidade']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($autor['au_pais']): ?>
                                <p class="card-text">
                                    <strong>País:</strong> <?php echo htmlspecialchars($autor['au_pais']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($autor['data_nascimento']): ?>
                                <p class="card-text">
                                    <strong>Nascimento:</strong> <?php echo date('d/m/Y', strtotime($autor['data_nascimento'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <p class="card-text">
                                <strong>Livros:</strong> 
                                <span class="badge bg-success"><?php echo $autor['total_livros']; ?></span>
                            </p>
                            
                            <?php if ($autor['biografia']): ?>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr($autor['biografia'], 0, 100)); ?>
                                        <?php if (strlen($autor['biografia']) > 100): ?>...<?php endif; ?>
                                    </small>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-outline-primary btn-sm" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning btn-sm" title="Editar" 
                                        onclick="editarAutor(<?php echo $autor['au_cod']; ?>, '<?php echo htmlspecialchars($autor['au_nome'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($autor['nacionalidade'] ?? '', ENT_QUOTES); ?>', '<?php echo $autor['data_nascimento'] ?? ''; ?>', '<?php echo htmlspecialchars($autor['biografia'] ?? '', ENT_QUOTES); ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" title="Eliminar" 
                                        onclick="eliminarAutor(<?php echo $autor['au_cod']; ?>, '<?php echo htmlspecialchars($autor['au_nome'], ENT_QUOTES); ?>')">
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
            <i class="bi bi-person" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhum autor encontrado</h3>
            <p class="text-muted">Adicione o primeiro autor à biblioteca.</p>
        </div>
        <?php endif; ?>
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
            document.getElementById('nacionalidade').value = '';
            document.getElementById('data_nascimento').value = '';
            document.getElementById('biografia').value = '';
            document.getElementById('nome').focus();
        }

        // Focar no campo nome quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nome').focus();
        });

        // Função para editar autor
        function editarAutor(id, nome, nacionalidade, dataNascimento, biografia) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_nacionalidade').value = nacionalidade;
            document.getElementById('edit_data_nascimento').value = dataNascimento;
            document.getElementById('edit_biografia').value = biografia;
            
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        // Função para eliminar autor
        function eliminarAutor(id, nome) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nome').textContent = nome;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Autor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nome" class="form-label">Nome do Autor *</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                                <div class="form-text">Nome completo do autor</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_nacionalidade" class="form-label">Nacionalidade</label>
                                <input type="text" class="form-control" id="edit_nacionalidade" name="nacionalidade" 
                                       placeholder="Ex: Portuguesa">
                                <div class="form-text">Ex: Portuguesa, Brasileira, Espanhola</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="edit_data_nascimento" name="data_nascimento">
                                <div class="form-text">Opcional - Data de nascimento do autor</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_biografia" class="form-label">Biografia</label>
                            <textarea class="form-control" id="edit_biografia" name="biografia" rows="4" 
                                      placeholder="Breve biografia do autor..."></textarea>
                            <div class="form-text">Opcional - Breve descrição sobre o autor</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Atualizar Autor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Eliminação -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="eliminar">
                        <input type="hidden" name="id" id="delete_id">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                        </div>
                        
                        <p>Tem certeza que deseja eliminar o autor <strong id="delete_nome"></strong>?</p>
                        
                        <p class="text-muted">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                Se o autor tiver livros associados, a eliminação será bloqueada.
                            </small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar Autor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
