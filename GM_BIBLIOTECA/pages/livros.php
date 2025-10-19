<?php
/**
 * Página de Gestão de Livros - Biblioteca Ginestal Machado
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de adição/edição/eliminação de livro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        try {
            $titulo = $_POST['titulo'] ?? '';
            $autor_id = $_POST['autor_id'] ?? '';
            $editora_id = $_POST['editora_id'] ?? '';
            $genero_id = $_POST['genero_id'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $edicao = $_POST['edicao'] ?? '';
            $ano_publicacao = $_POST['ano_publicacao'] ?? '';
            $numero_paginas = $_POST['numero_paginas'] ?? '';
            $quantidade_total = $_POST['quantidade_total'] ?? 1;
            $localizacao = $_POST['localizacao'] ?? '';
            $preco = $_POST['preco'] ?? '';
            
            // Validação de campos obrigatórios
            if (empty($titulo) || empty($autor_id) || empty($editora_id) || empty($genero_id)) {
                throw new Exception("Campos obrigatórios (Título, Autor, Editora, Género) não preenchidos.");
            }
            
            // Verificar se o ISBN já existe (se fornecido)
            if (!empty($isbn)) {
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM livro WHERE li_isbn = ?");
                $stmt->execute([$isbn]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['total'] > 0) {
                    throw new Exception("Já existe um livro com o ISBN '$isbn' na biblioteca. Por favor, verifique o ISBN ou deixe o campo vazio se não souber.");
                }
            }
            
            $sql = "INSERT INTO livro (li_titulo, li_autor, li_editora, li_genero, li_isbn, li_edicao, li_ano, li_pag, li_qtd_total, li_qtd_disponivel, li_localizacao, li_preco, li_data_aquisicao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $titulo, $autor_id, $editora_id, $genero_id, $isbn, $edicao, 
                $ano_publicacao, $numero_paginas, $quantidade_total, 
                $quantidade_total, $localizacao, $preco
            ]);
            
            $mensagem = "Livro adicionado com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            // Tratar erros específicos de violação de integridade
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'uk_livro_isbn') !== false) {
                    $mensagem = "Erro: Já existe um livro com este ISBN na biblioteca. Por favor, verifique o ISBN ou deixe o campo vazio se não souber.";
                } else {
                    $mensagem = "Erro de integridade dos dados: " . $e->getMessage();
                }
            } else {
                $mensagem = "Erro ao adicionar livro: " . $e->getMessage();
            }
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar edição de livro
    elseif ($_POST['acao'] == 'editar') {
        try {
            $id = $_POST['id'] ?? '';
            $titulo = $_POST['titulo'] ?? '';
            $autor_id = $_POST['autor_id'] ?? '';
            $editora_id = $_POST['editora_id'] ?? '';
            $genero_id = $_POST['genero_id'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $edicao = $_POST['edicao'] ?? '';
            $ano_publicacao = $_POST['ano_publicacao'] ?? '';
            $numero_paginas = $_POST['numero_paginas'] ?? '';
            $quantidade_total = $_POST['quantidade_total'] ?? 1;
            $localizacao = $_POST['localizacao'] ?? '';
            $preco = $_POST['preco'] ?? '';

            if (empty($id) || empty($titulo) || empty($autor_id) || empty($editora_id)) {
                throw new Exception("Campos obrigatórios (ID, Título, Autor, Editora) não preenchidos.");
            }

            // Verificar se o ISBN já existe em outro livro (se fornecido)
            if (!empty($isbn)) {
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM livro WHERE li_isbn = ? AND li_cod != ?");
                $stmt->execute([$isbn, $id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['total'] > 0) {
                    throw new Exception("Já existe outro livro com o ISBN '$isbn' na biblioteca. Por favor, verifique o ISBN ou deixe o campo vazio se não souber.");
                }
            }

            // Lógica para ajustar a quantidade disponível
            $stmt = $conn->prepare("SELECT li_qtd_total, li_qtd_disponivel FROM livro WHERE li_cod = ?");
            $stmt->execute([$id]);
            $livro_atual = $stmt->fetch();
            $diff_qtd = $quantidade_total - $livro_atual['li_qtd_total'];
            $nova_qtd_disponivel = $livro_atual['li_qtd_disponivel'] + $diff_qtd;

            $sql = "UPDATE livro SET 
                        li_titulo = ?, li_autor = ?, li_editora = ?, li_genero = ?, 
                        li_isbn = ?, li_edicao = ?, li_ano = ?, li_pag = ?, 
                        li_qtd_total = ?, li_qtd_disponivel = ?, li_localizacao = ?, li_preco = ?
                    WHERE li_cod = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $titulo, $autor_id, $editora_id, $genero_id, $isbn, $edicao, 
                $ano_publicacao, $numero_paginas, $quantidade_total, 
                $nova_qtd_disponivel, $localizacao, $preco, $id
            ]);

            $mensagem = "Livro actualizado com sucesso!";
            $tipo_mensagem = "success";

        } catch (PDOException $e) {
            // Tratar erros específicos de violação de integridade
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'uk_livro_isbn') !== false) {
                    $mensagem = "Erro: Já existe outro livro com este ISBN na biblioteca. Por favor, verifique o ISBN ou deixe o campo vazio se não souber.";
                } else {
                    $mensagem = "Erro de integridade dos dados: " . $e->getMessage();
                }
            } else {
                $mensagem = "Erro ao atualizar livro: " . $e->getMessage();
            }
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }

    // Processar eliminação de livro
    elseif ($_POST['acao'] == 'eliminar') {
        try {
            $id = $_POST['id'] ?? '';

            if (empty($id)) {
                throw new Exception("ID do livro é obrigatório!");
            }

            // Verificar se o livro tem requisições associadas
            if ($db->tabelaExiste('requisicao')) {
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM requisicao WHERE re_livro = ?");
                $stmt->execute([$id]);
                $total_req = $stmt->fetch()['total'];
                
                if ($total_req > 0) {
                    throw new Exception("Não é possível eliminar este livro pois tem $total_req requisição(ões) associada(s).");
                }
            }

            $sql = "DELETE FROM livro WHERE li_cod = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            $mensagem = "Livro eliminado com sucesso!";
            $tipo_mensagem = "success";

        } catch (PDOException $e) {
            $mensagem = "Erro ao eliminar livro: " . $e->getMessage();
            $tipo_mensagem = "danger";
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter dados para os selects
$autores = [];
$editoras = [];
$generos = [];

try {
    if ($db->tabelaExiste('autor')) {
        $stmt = $conn->query("SELECT au_cod as id, au_nome as nome FROM autor ORDER BY au_nome");
        $autores = $stmt->fetchAll();
    }
    
    if ($db->tabelaExiste('editora')) {
        $stmt = $conn->query("SELECT ed_cod as id, ed_nome as nome FROM editora ORDER BY ed_nome");
        $editoras = $stmt->fetchAll();
    }
    
    if ($db->tabelaExiste('genero')) {
        $stmt = $conn->query("SELECT ge_genero as id, ge_genero as nome FROM genero ORDER BY ge_genero");
        $generos = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar dados: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Obter lista de livros
$livros = [];
try {
    if ($db->tabelaExiste('livro')) {
        $sql = "SELECT l.*, a.au_nome as autor_nome, e.ed_nome as editora_nome, g.ge_genero as genero_nome 
                FROM livro l 
                LEFT JOIN autor a ON l.li_autor = a.au_cod 
                LEFT JOIN editora e ON l.li_editora = e.ed_cod 
                LEFT JOIN genero g ON l.li_genero = g.ge_genero 
                ORDER BY l.li_titulo";
        $stmt = $conn->query($sql);
        $livros = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar livros: " . $e->getMessage();
    $tipo_mensagem = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Livros - Biblioteca Ginestal Machado</title>
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
                        <a class="nav-link active" href="livros.php">
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
                        <a class="nav-link dropdown-toggle" href="#" id="gestaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="gestaoDropdown">
                            <li><a class="dropdown-item" href="editoras.php"><i class="bi bi-building"></i> Editoras</a></li>
                            <li><a class="dropdown-item" href="generos.php"><i class="bi bi-tags"></i> Gêneros</a></li>
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

        <!-- Formulário de Adicionar Livro -->
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-plus-circle"></i> Adicionar Novo Livro
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="titulo" class="form-label">Título do Livro *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" placeholder="Ex: 978-972-0-04555-4">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="autor_id" class="form-label">Autor *</label>
                        <select class="form-select" id="autor_id" name="autor_id" required>
                            <option value="">Selecione um autor</option>
                            <?php foreach ($autores as $autor): ?>
                                <option value="<?php echo $autor['id']; ?>"><?php echo htmlspecialchars($autor['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="editora_id" class="form-label">Editora *</label>
                        <select class="form-select" id="editora_id" name="editora_id" required>
                            <option value="">Selecione uma editora</option>
                            <?php foreach ($editoras as $editora): ?>
                                <option value="<?php echo $editora['id']; ?>"><?php echo htmlspecialchars($editora['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="genero_id" class="form-label">Género *</label>
                        <select class="form-select" id="genero_id" name="genero_id" required>
                            <option value="">Selecione um género</option>
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?php echo $genero['id']; ?>"><?php echo htmlspecialchars($genero['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="edicao" class="form-label">Edição</label>
                        <input type="text" class="form-control" id="edicao" name="edicao" placeholder="Ex: 1ª">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ano_publicacao" class="form-label">Ano de Publicação</label>
                        <input type="number" class="form-control" id="ano_publicacao" name="ano_publicacao" placeholder="<?php echo date('Y'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="numero_paginas" class="form-label">Nº de Páginas</label>
                        <input type="number" class="form-control" id="numero_paginas" name="numero_paginas">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="preco" class="form-label">Preço de Aquisição</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" step="0.01" class="form-control" id="preco" name="preco">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantidade_total" class="form-label">Quantidade em Stock *</label>
                        <input type="number" class="form-control" id="quantidade_total" name="quantidade_total" value="1" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localizacao" class="form-label">Localização na Biblioteca</label>
                        <input type="text" class="form-control" id="localizacao" name="localizacao" placeholder="Ex: Prateleira A3, Secção 2">
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-custom btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Adicionar Livro
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

        <!-- Lista de Livros -->
        <?php if (!empty($livros)): ?>
        <div class="content-section">
            <h2 class="mb-4">
                <i class="bi bi-list-ul"></i> Livros no Acervo
                <span class="badge bg-primary"><?php echo count($livros); ?></span>
            </h2>
            
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Editora</th>
                            <th>Género</th>
                            <th>ISBN</th>
                            <th>Ano</th>
                            <th>Disponível</th>
                            <th>Localização</th>
                            <th>Acções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livros as $livro): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($livro['li_titulo']); ?></strong>
                                <?php if ($livro['li_edicao']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($livro['li_edicao']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($livro['autor_nome'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($livro['editora_nome'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($livro['genero_nome'] ?? 'N/A'); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($livro['li_isbn'] ?? 'N/A'); ?></td>
                            <td><?php echo $livro['li_ano'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $livro['li_qtd_disponivel'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $livro['li_qtd_disponivel']; ?> / <?php echo $livro['li_qtd_total']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($livro['li_localizacao'] ?? 'N/A'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Ver detalhes" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $livro['li_cod']; ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Editar" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $livro['li_cod']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#eliminarModal<?php echo $livro['li_cod']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="content-section text-center">
            <i class="bi bi-book" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nenhum livro encontrado</h3>
            <p class="text-muted">Adicione o primeiro livro ao acervo da biblioteca.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modais para Detalhes, Edição e Eliminação -->
    <?php foreach ($livros as $livro): ?>
    <!-- Modal de Detalhes -->
    <div class="modal fade" id="detalhesModal<?php echo $livro['li_cod']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Livro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Título:</strong> <?php echo htmlspecialchars($livro['li_titulo']); ?></p>
                            <p><strong>Autor:</strong> <?php echo htmlspecialchars($livro['autor_nome'] ?? 'N/A'); ?></p>
                            <p><strong>Editora:</strong> <?php echo htmlspecialchars($livro['editora_nome'] ?? 'N/A'); ?></p>
                            <p><strong>Género:</strong> <?php echo htmlspecialchars($livro['genero_nome'] ?? 'N/A'); ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livro['li_isbn'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Edição:</strong> <?php echo htmlspecialchars($livro['li_edicao'] ?? 'N/A'); ?></p>
                            <p><strong>Ano de Publicação:</strong> <?php echo $livro['li_ano'] ?? 'N/A'; ?></p>
                            <p><strong>Nº de Páginas:</strong> <?php echo $livro['li_pag'] ?? 'N/A'; ?></p>
                            <p><strong>Preço de Aquisição:</strong> €<?php echo number_format($livro['li_preco'] ?? 0, 2); ?></p>
                            <p><strong>Localização:</strong> <?php echo htmlspecialchars($livro['li_localizacao'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <p><strong>Quantidade Total:</strong> <?php echo $livro['li_qtd_total']; ?></p>
                            <p><strong>Quantidade Disponível:</strong> <?php echo $livro['li_qtd_disponivel']; ?></p>
                            <p><strong>Data de Aquisição:</strong> <?php echo $livro['li_data_aquisicao'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editarModal<?php echo $livro['li_cod']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Livro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?php echo $livro['li_cod']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titulo_<?php echo $livro['li_cod']; ?>" class="form-label">Título do Livro *</label>
                                <input type="text" class="form-control" id="titulo_<?php echo $livro['li_cod']; ?>" name="titulo" value="<?php echo htmlspecialchars($livro['li_titulo']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="isbn_<?php echo $livro['li_cod']; ?>" class="form-label">ISBN</label>
                                <input type="text" class="form-control" id="isbn_<?php echo $livro['li_cod']; ?>" name="isbn" value="<?php echo htmlspecialchars($livro['li_isbn']); ?>" placeholder="Ex: 978-972-0-04555-4">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="autor_id_<?php echo $livro['li_cod']; ?>" class="form-label">Autor *</label>
                                <select class="form-select" id="autor_id_<?php echo $livro['li_cod']; ?>" name="autor_id" required>
                                    <option value="">Selecione um autor</option>
                                    <?php foreach ($autores as $autor): ?>
                                        <option value="<?php echo $autor['id']; ?>" <?php echo ($autor['id'] == $livro['li_autor']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($autor['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="editora_id_<?php echo $livro['li_cod']; ?>" class="form-label">Editora *</label>
                                <select class="form-select" id="editora_id_<?php echo $livro['li_cod']; ?>" name="editora_id" required>
                                    <option value="">Selecione uma editora</option>
                                    <?php foreach ($editoras as $editora): ?>
                                        <option value="<?php echo $editora['id']; ?>" <?php echo ($editora['id'] == $livro['li_editora']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($editora['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="genero_id_<?php echo $livro['li_cod']; ?>" class="form-label">Género *</label>
                                <select class="form-select" id="genero_id_<?php echo $livro['li_cod']; ?>" name="genero_id" required>
                                    <option value="">Selecione um género</option>
                                    <?php foreach ($generos as $genero): ?>
                                        <option value="<?php echo $genero['id']; ?>" <?php echo ($genero['id'] == $livro['li_genero']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($genero['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="edicao_<?php echo $livro['li_cod']; ?>" class="form-label">Edição</label>
                                <input type="text" class="form-control" id="edicao_<?php echo $livro['li_cod']; ?>" name="edicao" value="<?php echo htmlspecialchars($livro['li_edicao']); ?>" placeholder="Ex: 1ª">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="ano_publicacao_<?php echo $livro['li_cod']; ?>" class="form-label">Ano de Publicação</label>
                                <input type="number" class="form-control" id="ano_publicacao_<?php echo $livro['li_cod']; ?>" name="ano_publicacao" value="<?php echo $livro['li_ano']; ?>" placeholder="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="numero_paginas_<?php echo $livro['li_cod']; ?>" class="form-label">Nº de Páginas</label>
                                <input type="number" class="form-control" id="numero_paginas_<?php echo $livro['li_cod']; ?>" name="numero_paginas" value="<?php echo $livro['li_pag']; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="preco_<?php echo $livro['li_cod']; ?>" class="form-label">Preço de Aquisição</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" step="0.01" class="form-control" id="preco_<?php echo $livro['li_cod']; ?>" name="preco" value="<?php echo $livro['li_preco']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantidade_total_<?php echo $livro['li_cod']; ?>" class="form-label">Quantidade em Stock *</label>
                                <input type="number" class="form-control" id="quantidade_total_<?php echo $livro['li_cod']; ?>" name="quantidade_total" value="<?php echo $livro['li_qtd_total']; ?>" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="localizacao_<?php echo $livro['li_cod']; ?>" class="form-label">Localização na Biblioteca</label>
                                <input type="text" class="form-control" id="localizacao_<?php echo $livro['li_cod']; ?>" name="localizacao" value="<?php echo htmlspecialchars($livro['li_localizacao']); ?>" placeholder="Ex: Prateleira A3, Secção 2">
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar Livro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Eliminação -->
    <div class="modal fade" id="eliminarModal<?php echo $livro['li_cod']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja eliminar o livro "<strong><?php echo htmlspecialchars($livro['li_titulo']); ?></strong>"?</p>
                    <p class="text-muted"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="acao" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $livro['li_cod']; ?>">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

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
        
        function limparFormulario() {
            document.getElementById('titulo').value = '';
            document.getElementById('isbn').value = '';
            document.getElementById('autor_id').value = '';
            document.getElementById('editora_id').value = '';
            document.getElementById('genero_id').value = '';
            document.getElementById('edicao').value = '';
            document.getElementById('ano_publicacao').value = '';
            document.getElementById('numero_paginas').value = '';
            document.getElementById('quantidade_total').value = '1';
            document.getElementById('localizacao').value = '';
            document.getElementById('preco').value = '';
        }
    </script>
</body>
</html>