<?php
// Incluir configuração da base de dados
require_once 'config/database.php';

// Carregar estatísticas iniciais
$db = new Database();
$conn = $db->getConnection();
$estatisticas = [];

if ($conn) {
    $estatisticas = $db->getEstatisticas();
} else {
    $estatisticas = [
        'total_livros' => 0,
        'total_utentes' => 0,
        'emprestimos_ativos' => 0,
        'emprestimos_atrasados' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Ginestal Machado</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' fill='%23000'/><text x='50' y='70' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='%23B22222'>A</text><rect x='20' y='80' width='60' height='3' fill='%23FF8C00'/><rect x='25' y='85' width='50' height='2' fill='%23DC143C'/><rect x='30' y='90' width='40' height='2' fill='%23FF69B4'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
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
        
        .navbar, .footer {
            background: var(--primary-color);
        }
        .footer {
            color: #fff;
            padding: 30px 0;
        }
        .footer .social-icons a {
            color: #fff;
            margin-right: 15px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book-half"></i> Biblioteca Ginestal Machado
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/livros.php">
                            <i class="bi bi-book"></i> Livros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/autores.php">
                            <i class="bi bi-person"></i> Autores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/utentes.php">
                            <i class="bi bi-people"></i> Utentes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/requisicoes.php">
                            <i class="bi bi-arrow-left-right"></i> Requisições
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Gestão
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="gestaoDropdown">
                            <li><a class="dropdown-item" href="pages/editoras.php"><i class="bi bi-building"></i> Editoras</a></li>
                            <li><a class="dropdown-item" href="pages/generos.php"><i class="bi bi-tags"></i> Géneros</a></li>
                            <li><a class="dropdown-item" href="pages/codigos_postais.php"><i class="bi bi-geo-alt"></i> Códigos Postais</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <div class="card shadow-lg border-0" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-body py-5">
                        <h1 class="display-4 text-primary mb-3">
                            <i class="bi bi-heart-fill text-danger"></i>
                            Bem-vinda Sra. Cassilda
                        </h1>
                        <h2 class="h3 text-secondary mb-4">à Biblioteca Ginestal Machado</h2>
                        <p class="lead text-muted">
                            Gerencie a sua biblioteca escolar de forma eficiente e moderna
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="card stats-card text-center border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body">
                        <i class="bi bi-book text-primary mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="text-primary"><?php echo number_format($estatisticas['total_livros']); ?></h3>
                        <p class="text-muted mb-0">Total de Livros</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card text-center border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body">
                        <i class="bi bi-people text-success mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="text-success"><?php echo number_format($estatisticas['total_utentes']); ?></h3>
                        <p class="text-muted mb-0">Total de Utentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card text-center border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body">
                        <i class="bi bi-arrow-right-circle text-info mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="text-info"><?php echo number_format($estatisticas['emprestimos_ativos']); ?></h3>
                        <p class="text-muted mb-0">Empréstimos Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card text-center border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body">
                        <i class="bi bi-exclamation-triangle text-warning mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="text-warning"><?php echo number_format($estatisticas['emprestimos_atrasados']); ?></h3>
                        <p class="text-muted mb-0">Empréstimos em Atraso</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body text-center">
                        <i class="bi bi-book text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Gestão de Livros</h5>
                        <p class="card-text text-muted">Adicione, edite e gerencie o catálogo de livros da biblioteca.</p>
                        <a href="pages/livros.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> Aceder
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body text-center">
                        <i class="bi bi-people text-success mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Gestão de Utentes</h5>
                        <p class="card-text text-muted">Cadastre e gerencie os utentes da biblioteca escolar.</p>
                        <a href="pages/utentes.php" class="btn btn-success">
                            <i class="bi bi-arrow-right"></i> Aceder
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.9);">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-left-right text-info mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Requisições</h5>
                        <p class="card-text text-muted">Gerencie empréstimos e devoluções de livros.</p>
                        <a href="pages/requisicoes.php" class="btn btn-info">
                            <i class="bi bi-arrow-right"></i> Aceder
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto" style="background: var(--primary-color);">
        <div class="container d-flex justify-content-between align-items-center">
            <span>© 2024 Escola Ginestal Machado. Todos os direitos reservados.</span>
            <div class="social-icons">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-envelope"></i></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar animações aos cards
            const cards = document.querySelectorAll('.feature-card, .stats-card, .quick-access');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });

            // Auto-refresh das estatísticas a cada 30 segundos
            setInterval(function() {
                fetch('ajax/refresh_stats.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Atualizar estatísticas sem recarregar a página
                            document.querySelector('.stats-card:nth-child(1) h3').textContent = data.stats.total_livros.toLocaleString();
                            document.querySelector('.stats-card:nth-child(2) h3').textContent = data.stats.total_utentes.toLocaleString();
                            document.querySelector('.stats-card:nth-child(3) h3').textContent = data.stats.emprestimos_ativos.toLocaleString();
                            document.querySelector('.stats-card:nth-child(4) h3').textContent = data.stats.emprestimos_atrasados.toLocaleString();
                        }
                    })
                    .catch(error => console.log('Erro ao atualizar estatísticas:', error));
            }, 30000);

            // Adicionar tooltips do Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
