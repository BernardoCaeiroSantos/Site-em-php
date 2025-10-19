<?php
/**
 * Página de Gestão de Requisições - Biblioteca Ginestal Machado
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de nova requisição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $utente_id = $_POST['utente_id'] ?? '';
            $livro_id = $_POST['livro_id'] ?? '';
            $dias_emprestimo = $_POST['dias_emprestimo'] ?? 15;
            
            if (empty($utente_id) || empty($livro_id)) {
                throw new Exception("Utente e livro são obrigatórios!");
            }
            
            // Verificar se o livro está disponível
            $stmt = $conn->prepare("SELECT li_qtd_disponivel FROM livro WHERE li_cod = ?");
            $stmt->execute([$livro_id]);
            $livro = $stmt->fetch();
            
            if (!$livro || $livro['li_qtd_disponivel'] <= 0) {
                throw new Exception("Livro não disponível para requisição!");
            }
            
            // Calcular data de devolução
            $data_devolucao = date('Y-m-d', strtotime("+$dias_emprestimo days"));
            
            // Inserir requisição
            $sql = "INSERT INTO requisicao (re_ut_cod, re_li_cod, re_data_requisicao, re_data_devolucao) 
                    VALUES (?, ?, NOW(), ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$utente_id, $livro_id, $data_devolucao]);
            
            // Atualizar quantidade disponível do livro
            $sql = "UPDATE livro SET li_qtd_disponivel = li_qtd_disponivel - 1 WHERE li_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$livro_id]);
            
            $mensagem = "Requisição criada com sucesso! Devolução prevista: " . date('d/m/Y', strtotime($data_devolucao));
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao criar requisição: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    if ($_POST['acao'] == 'devolver') {
        try {
            $requisicao_id = $_POST['requisicao_id'] ?? '';
            
            if (empty($requisicao_id)) {
                throw new Exception("ID da requisição é obrigatório!");
            }
            
            // Obter informações da requisição
            $stmt = $conn->prepare("SELECT re_li_cod FROM requisicao WHERE re_cod = ?");
            $stmt->execute([$requisicao_id]);
            $requisicao = $stmt->fetch();
            
            if (!$requisicao) {
                throw new Exception("Requisição não encontrada!");
            }
            
            // Atualizar requisição
            $sql = "UPDATE requisicao SET re_data_devolucao = NOW() WHERE re_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$requisicao_id]);
            
            // Atualizar quantidade disponível do livro
            $sql = "UPDATE livro SET li_qtd_disponivel = li_qtd_disponivel + 1 WHERE li_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$requisicao['re_li_cod']]);
            
            $mensagem = "Livro devolvido com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao devolver livro: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    if ($_POST['acao'] == 'renovar') {
        try {
            $requisicao_id = $_POST['requisicao_id'] ?? '';
            $dias_renovacao = $_POST['dias_renovacao'] ?? 15;
            
            if (empty($requisicao_id)) {
                throw new Exception("ID da requisição é obrigatório!");
            }
            
            // Obter informações da requisição atual
            $stmt = $conn->prepare("SELECT re_data_devolucao FROM requisicao WHERE re_cod = ?");
            $stmt->execute([$requisicao_id]);
            $requisicao = $stmt->fetch();
            
            if (!$requisicao) {
                throw new Exception("Requisição não encontrada!");
            }
            
            // Calcular nova data de devolução
            $data_atual = $requisicao['re_data_devolucao'] ?: date('Y-m-d');
            $nova_data_devolucao = date('Y-m-d', strtotime($data_atual . " +$dias_renovacao days"));
            
            // Atualizar requisição com nova data
            $sql = "UPDATE requisicao SET re_data_devolucao = ? WHERE re_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nova_data_devolucao, $requisicao_id]);
            
            $mensagem = "Empréstimo renovado com sucesso! Nova data de devolução: " . date('d/m/Y', strtotime($nova_data_devolucao));
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao renovar empréstimo: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    if ($_POST['acao'] == 'eliminar') {
        try {
            $requisicao_id = $_POST['requisicao_id'] ?? '';
            
            if (empty($requisicao_id)) {
                throw new Exception("ID da requisição é obrigatório!");
            }
            
            // Obter informações da requisição
            $stmt = $conn->prepare("SELECT re_li_cod, re_data_devolucao FROM requisicao WHERE re_cod = ?");
            $stmt->execute([$requisicao_id]);
            $requisicao = $stmt->fetch();
            
            if (!$requisicao) {
                throw new Exception("Requisição não encontrada!");
            }
            
            // Se não foi devolvido, restaurar quantidade
            if (empty($requisicao['re_data_devolucao'])) {
                $sql = "UPDATE livro SET li_qtd_disponivel = li_qtd_disponivel + 1 WHERE li_cod = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$requisicao['re_li_cod']]);
            }
            
            // Eliminar requisição
            $sql = "DELETE FROM requisicao WHERE re_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$requisicao_id]);
            
            $mensagem = "Requisição eliminada com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao eliminar requisição: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter utentes e livros disponíveis para os selects
$utentes = [];
$livros_disponiveis = [];

try {
    if ($db->tabelaExiste('utente')) {
        $stmt = $conn->query("SELECT ut_cod, ut_nome, ut_tipo FROM utente ORDER BY ut_nome");
        $utentes = $stmt->fetchAll();
    }
    
    if ($db->tabelaExiste('livro')) {
        $sql = "SELECT l.li_cod, l.li_titulo, a.au_nome, l.li_qtd_disponivel 
                FROM livro l 
                LEFT JOIN autor a ON l.li_autor = a.au_cod 
                WHERE l.li_qtd_disponivel > 0 
                ORDER BY l.li_titulo";
        $stmt = $conn->query($sql);
        $livros_disponiveis = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar dados: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Obter lista de requisições
$requisicoes = [];
try {
    if ($db->tabelaExiste('requisicao')) {
        $sql = "SELECT r.*, 
                       u.ut_nome as utente_nome, 
                       u.ut_cod as numero_utente, 
                       u.ut_tipo as tipo_utente, 
                       l.li_titulo as livro_titulo, 
                       a.au_nome as autor_nome,
                       CASE 
                           WHEN r.re_data_devolucao IS NULL OR r.re_data_devolucao = '0000-00-00' THEN NULL
                           ELSE DATEDIFF(r.re_data_devolucao, CURDATE())
                       END as dias_restantes,
                       CASE 
                           WHEN r.re_data_devolucao IS NULL OR r.re_data_devolucao = '0000-00-00' THEN 'ativa'
                           WHEN DATEDIFF(r.re_data_devolucao, CURDATE()) < 0 THEN 'atrasada'
                           ELSE 'ativa'
                       END as status
                FROM requisicao r 
                LEFT JOIN utente u ON r.re_ut_cod = u.ut_cod 
                LEFT JOIN livro l ON r.re_li_cod = l.li_cod 
                LEFT JOIN autor a ON l.li_autor = a.au_cod 
                ORDER BY r.re_data_requisicao DESC";
        $stmt = $conn->query($sql);
        $requisicoes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar requisições: " . $e->getMessage();
    $tipo_mensagem = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Requisições - Biblioteca Ginestal Machado</title>
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

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .status-ativa { background-color: #d4edda; }
        .status-devolvida { background-color: #cce5ff; }
        .status-atrasada { background-color: #f8d7da; }
        .status-perdida { background-color: #fff3cd; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="#">
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
                        <a class="nav-link active" href="requisicoes.php">
                            <i class="bi bi-arrow-left-right"></i> Requisições
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="gestaoDropdown">
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

        <!-- Formulário de Nova Requisição -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-plus-circle"></i> Nova Requisição
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="utente_id" class="form-label">Utente *</label>
                        <select class="form-select" id="utente_id" name="utente_id" required>
                            <option value="">Selecione um utente</option>
                            <?php foreach ($utentes as $utente): ?>
                                <option value="<?php echo $utente['ut_cod']; ?>">
                                    <?php echo htmlspecialchars($utente['ut_nome'] . ' (' . $utente['ut_cod'] . ') - ' . ucfirst($utente['ut_tipo'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="livro_id" class="form-label">Livro *</label>
                        <select class="form-select" id="livro_id" name="livro_id" required>
                            <option value="">Selecione um livro</option>
                            <?php foreach ($livros_disponiveis as $livro): ?>
                                <option value="<?php echo $livro['li_cod']; ?>">
                                    <?php echo htmlspecialchars($livro['li_titulo'] . ' (' . $livro['li_qtd_disponivel'] . ' disp.)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dias_emprestimo" class="form-label">Prazo de Empréstimo (dias) *</label>
                        <select class="form-select" id="dias_emprestimo" name="dias_emprestimo" required>
                            <option value="7">7 dias</option>
                            <option value="15" selected>15 dias</option>
                            <option value="30">30 dias</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data de Devolução Prevista</label>
                        <div class="form-control-plaintext">
                            <strong id="data-devolucao"><?php echo date('d/m/Y', strtotime('+15 days')); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Criar Requisição
                    </button>
                    <a href="../index.php" class="btn btn-outline-secondary btn-custom">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Requisições -->
        <?php if (!empty($requisicoes)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-list-ul"></i> Requisições
                <span class="badge bg-primary"><?php echo count($requisicoes); ?></span>
            </h2>
            
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utente</th>
                            <th>Livro</th>
                            <th>Data Requisição</th>
                            <th>Devolução Prevista</th>
                            <th>Status</th>
                            <th>Dias Restantes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requisicoes as $req): ?>
                        <tr class="status-<?php echo $req['status']; ?>">
                            <td><strong>#<?php echo $req['re_cod']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($req['utente_nome']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($req['numero_utente']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($req['livro_titulo']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($req['autor_nome']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($req['re_data_requisicao'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($req['re_data_devolucao'])); ?></td>
                            <td>
                                <?php
                                $cores_status = [
                                    'ativa' => 'success',
                                    'devolvida' => 'primary',
                                    'atrasada' => 'danger',
                                    'perdida' => 'warning'
                                ];
                                $cor = $cores_status[$req['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $cor; ?>">
                                    <?php echo ucfirst($req['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($req['status'] == 'ativa'): ?>
                                    <?php if ($req['dias_restantes'] !== null): ?>
                                        <?php if ($req['dias_restantes'] > 0): ?>
                                            <span class="badge bg-success"><?php echo $req['dias_restantes']; ?> dias</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo abs($req['dias_restantes']); ?> dias em atraso</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Data não definida</span>
                                    <?php endif; ?>
                                <?php elseif ($req['status'] == 'atrasada'): ?>
                                    <span class="badge bg-danger"><?php echo abs($req['dias_restantes']); ?> dias em atraso</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if ($req['status'] == 'ativa'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja devolver este livro?')">
                                            <input type="hidden" name="acao" value="devolver">
                                            <input type="hidden" name="requisicao_id" value="<?php echo $req['re_cod']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Devolver">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-warning" title="Renovar" data-bs-toggle="modal" data-bs-target="#renovarModal<?php echo $req['re_cod']; ?>">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja eliminar esta requisição?')">
                                        <input type="hidden" name="acao" value="eliminar">
                                        <input type="hidden" name="requisicao_id" value="<?php echo $req['re_cod']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modais de Renovação -->
        <?php foreach ($requisicoes as $req): ?>
        <?php if ($req['status'] == 'ativa'): ?>
        <div class="modal fade" id="renovarModal<?php echo $req['re_cod']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Renovar Empréstimo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <input type="hidden" name="acao" value="renovar">
                            <input type="hidden" name="requisicao_id" value="<?php echo $req['re_cod']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Livro:</strong> <?php echo htmlspecialchars($req['livro_titulo']); ?></label>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Utente:</strong> <?php echo htmlspecialchars($req['utente_nome']); ?></label>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dias_renovacao_<?php echo $req['re_cod']; ?>" class="form-label">Dias de Renovação *</label>
                                <select class="form-select" id="dias_renovacao_<?php echo $req['re_cod']; ?>" name="dias_renovacao" required>
                                    <option value="7">7 dias</option>
                                    <option value="15" selected>15 dias</option>
                                    <option value="30">30 dias</option>
                                    <option value="45">45 dias</option>
                                    <option value="60">60 dias</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Data de Devolução Atual:</label>
                                <p class="form-control-plaintext">
                                    <?php 
                                    if ($req['re_data_devolucao'] && $req['re_data_devolucao'] != '0000-00-00') {
                                        echo date('d/m/Y', strtotime($req['re_data_devolucao']));
                                    } else {
                                        echo 'Data não definida';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning">Renovar Empréstimo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="content-section text-center">
            <i class="bi bi-arrow-left-right" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhuma requisição encontrada</h3>
            <p class="text-muted">Crie a primeira requisição de empréstimo.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Atualizar data de devolução quando mudar o prazo
        document.getElementById('dias_emprestimo').addEventListener('change', function() {
            const dias = parseInt(this.value);
            const data = new Date();
            data.setDate(data.getDate() + dias);
            const dataFormatada = data.toLocaleDateString('pt-PT');
            document.getElementById('data-devolucao').textContent = dataFormatada;
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {Erro ao criar requisição: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`gm_biblioteca`.`requisicao`, CONSTRAINT `fk_req_exemplar` FOREIGN KEY (`re_lex_cod`) REFERENCES `livro_exemplar` (`lex_cod`) ON UPDATE CASCADE)Erro ao carregar requisições: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'lex.lex_numero' in 'field list'Erro ao carregar requisições: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'r.re_lex_cod' in 'on clause'Erro ao carregar requisições: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'r.re_lex_cod' in 'on clause'
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
