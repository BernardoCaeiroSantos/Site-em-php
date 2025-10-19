<?php
/**
 * Página de Gestão de Utentes - Biblioteca Ginestal Machado
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de adição/edição/eliminação de utente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $morada = trim($_POST['morada'] ?? '');
            $codigo_postal = trim($_POST['codigo_postal_id'] ?? '');
            $nif = trim($_POST['nif'] ?? '');
            $tipo_utente = trim($_POST['tipo_utente'] ?? 'aluno');
            
            // Validações
            if (empty($nome)) {
                throw new Exception("Nome é obrigatório!");
            }
            
            // Gerar cp_id (pode ser um valor padrão ou baseado em alguma lógica)
            $cp_id = !empty($codigo_postal) ? $codigo_postal : null;
            
            $sql = "INSERT INTO utente (ut_nome, ut_email, ut_tlm, ut_morada, ut_cod_postal, ut_nif, ut_tipo, cp_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nome, $email, $telefone, $morada, $codigo_postal, $nif, $tipo_utente, $cp_id]);
            
            $mensagem = "Utente '$nome' adicionado com sucesso!";
            $tipo_mensagem = "success";
            
            // Limpar formulário após sucesso
            $_POST = [];
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao adicionar utente: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar edição de utente
    elseif ($_POST['acao'] == 'editar') {
        try {
            $id = $_POST['id'];
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $telefone = trim($_POST['telefone']);
            $nif = trim($_POST['nif']);
            $tipo_utente = $_POST['tipo_utente'];
            $codigo_postal_id = !empty($_POST['codigo_postal_id']) ? $_POST['codigo_postal_id'] : null;
            $morada = trim($_POST['morada']);
            
            if (empty($nome)) {
                throw new Exception("Nome do utente é obrigatório!");
            }
            
            $sql = "UPDATE utente SET 
                    ut_nome = ?, 
                    ut_email = ?, 
                    ut_tlm = ?, 
                    ut_nif = ?, 
                    ut_tipo = ?, 
                    ut_cod_postal = ?, 
                    ut_morada = ? 
                    WHERE ut_cod = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $nome,
                !empty($email) ? $email : null,
                !empty($telefone) ? $telefone : null,
                !empty($nif) ? $nif : null,
                $tipo_utente,
                $codigo_postal_id,
                !empty($morada) ? $morada : null,
                $id
            ]);
            
            $mensagem = "Utente '$nome' actualizado com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao editar utente: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar eliminação de utente
    elseif ($_POST['acao'] == 'eliminar') {
        try {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                throw new Exception("ID do utente é obrigatório!");
            }
            
            // Verificar se o utente tem requisições ativas
            if ($db->tabelaExiste('requisicao')) {
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM requisicao WHERE re_ut_cod = ? AND re_data_devolucao IS NULL");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
                $total_requisicoes = $result['total'] ?? 0;
                
                if ($total_requisicoes > 0) {
                    throw new Exception("Não é possível eliminar este utente pois tem $total_requisicoes requisição(ões) ativa(s).");
                }
            }
            
            $sql = "DELETE FROM utente WHERE ut_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            
            $mensagem = "Utente eliminado com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao eliminar utente: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter lista de utentes
$utentes = [];
try {
    if ($db->tabelaExiste('utente')) {
        $sql = "SELECT u.*, 
                cp.cod_postal, 
                cp.cod_localidade as localidade,
                (SELECT COUNT(*) FROM requisicao r WHERE r.re_ut_cod = u.ut_cod AND r.re_data_devolucao IS NULL) as total_requisicoes
                FROM utente u 
                LEFT JOIN codigo_postal cp ON u.ut_cod_postal = cp.cod_postal 
                ORDER BY u.ut_nome";
        $stmt = $conn->query($sql);
        $utentes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar utentes: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Obter códigos postais para o select
$codigos_postais = [];
try {
    if ($db->tabelaExiste('codigo_postal')) {
        $sql = "SELECT cod_postal, cod_localidade FROM codigo_postal ORDER BY cod_postal";
        $stmt = $conn->query($sql);
        $codigos_postais = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Se houver erro, continuar sem códigos postais
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utentes - Biblioteca Ginestal Machado</title>
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

        .badge-tipo {
            font-size: 0.8rem;
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
                        <a class="nav-link active" href="utentes.php">
                            <i class="bi bi-people"></i> Utentes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requisicoes.php">
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
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show mt-3" role="alert">
                <?php echo htmlspecialchars($mensagem); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário de Adicionar Utente -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-person-plus"></i> Adicionar Novo Utente
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Ex: 912345678">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nif" class="form-label">NIF</label>
                        <input type="text" class="form-control" id="nif" name="nif" placeholder="Ex: 123456789">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo_utente" class="form-label">Tipo de Utente *</label>
                        <select class="form-select" id="tipo_utente" name="tipo_utente" required>
                            <option value="aluno">Aluno</option>
                            <option value="professor">Professor</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="externo">Externo</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="codigo_postal_id" class="form-label">Localização</label>
                        <select class="form-select" id="codigo_postal_id" name="codigo_postal_id">
                            <option value="">Selecione uma localidade</option>
                            <?php foreach ($codigos_postais as $cp): ?>
                                <option value="<?php echo htmlspecialchars($cp['cod_postal']); ?>">
                                    <?php echo htmlspecialchars($cp['cod_postal'] . ' - ' . $cp['cod_localidade']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="morada" class="form-label">Morada</label>
                    <textarea class="form-control" id="morada" name="morada" rows="2" placeholder="Endereço completo..."></textarea>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-person-plus"></i> Adicionar Utente
                    </button>
                    <a href="../index.php" class="btn btn-outline-secondary btn-custom">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Utentes -->
        <?php if (!empty($utentes)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-people"></i> Utentes Cadastrados
                <span class="badge bg-primary"><?php echo count($utentes); ?></span>
            </h2>
            
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>NIF</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Localização</th>
                            <th>Requisições</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utentes as $utente): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($utente['ut_nome']); ?></strong>
                            </td>
                            <td>
                                <?php
                                $cores = [
                                    'aluno' => 'primary',
                                    'professor' => 'success',
                                    'funcionario' => 'warning',
                                    'externo' => 'info'
                                ];
                                $cor = $cores[$utente['ut_tipo']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $cor; ?> badge-tipo">
                                    <?php echo ucfirst($utente['ut_tipo']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($utente['ut_nif'] ?? 'N/A'); ?>
                            </td>
                            <td><?php echo htmlspecialchars($utente['ut_email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($utente['ut_tlm'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($utente['localidade'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $utente['total_requisicoes']; ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditar" data-id="<?php echo $utente['ut_cod']; ?>" data-nome="<?php echo htmlspecialchars($utente['ut_nome']); ?>" data-email="<?php echo htmlspecialchars($utente['ut_email']); ?>" data-telefone="<?php echo htmlspecialchars($utente['ut_tlm']); ?>" data-morada="<?php echo htmlspecialchars($utente['ut_morada']); ?>" data-codigo-postal="<?php echo htmlspecialchars($utente['ut_cod_postal']); ?>" data-nif="<?php echo htmlspecialchars($utente['ut_nif']); ?>" data-tipo-utente="<?php echo htmlspecialchars($utente['ut_tipo']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#modalEliminar" data-id="<?php echo $utente['ut_cod']; ?>" data-nome="<?php echo htmlspecialchars($utente['ut_nome']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="content-section text-center">
            <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhum utente encontrado</h3>
            <p class="text-muted">Adicione o primeiro utente à biblioteca.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Editar Utente -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Utente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="edit_telefone" name="telefone" placeholder="Ex: 912345678">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_nif" class="form-label">NIF</label>
                                <input type="text" class="form-control" id="edit_nif" name="nif" placeholder="Ex: 123456789">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_tipo_utente" class="form-label">Tipo de Utente *</label>
                                <select class="form-select" id="edit_tipo_utente" name="tipo_utente" required>
                                    <option value="aluno">Aluno</option>
                                    <option value="professor">Professor</option>
                                    <option value="funcionario">Funcionário</option>
                                    <option value="externo">Externo</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo_postal_id" class="form-label">Localização</label>
                                <select class="form-select" id="edit_codigo_postal_id" name="codigo_postal_id">
                                    <option value="">Selecione uma localidade</option>
                                    <?php foreach ($codigos_postais as $cp): ?>
                                        <option value="<?php echo htmlspecialchars($cp['cod_postal']); ?>">
                                            <?php echo htmlspecialchars($cp['cod_postal'] . ' - ' . $cp['cod_localidade']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_morada" class="form-label">Morada</label>
                            <textarea class="form-control" id="edit_morada" name="morada" rows="2" placeholder="Endereço completo..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar Utente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Utente -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">Eliminar Utente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="acao" value="eliminar">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja eliminar o utente <strong id="delete_nome"></strong>?</p>
                        <p class="text-muted">Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar Utente</button>
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
        
        // Preencher dados do modal de edição
        document.getElementById('modalEditar').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nome = button.getAttribute('data-nome');
            var email = button.getAttribute('data-email');
            var telefone = button.getAttribute('data-telefone');
            var morada = button.getAttribute('data-morada');
            var codigoPostal = button.getAttribute('data-codigo-postal');
            var nif = button.getAttribute('data-nif');
            var tipoUtente = button.getAttribute('data-tipo-utente');
            
            var modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_nome').value = nome;
            modal.querySelector('#edit_email').value = email;
            modal.querySelector('#edit_telefone').value = telefone;
            modal.querySelector('#edit_morada').value = morada;
            modal.querySelector('#edit_codigo_postal_id').value = codigoPostal;
            modal.querySelector('#edit_nif').value = nif;
            modal.querySelector('#edit_tipo_utente').value = tipoUtente;
        });
        
        // Preencher dados do modal de eliminação
        document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nome = button.getAttribute('data-nome');
            
            var modal = this;
            modal.querySelector('#delete_id').value = id;
            modal.querySelector('#delete_nome').textContent = nome;
        });
    </script>
</body>
</html>
