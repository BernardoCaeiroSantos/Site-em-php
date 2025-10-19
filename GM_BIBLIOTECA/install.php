<?php
/**
 * Script de instalação para a Biblioteca Ginestal Machado
 * Este script detecta a estrutura atual e cria as tabelas necessárias
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("❌ Erro de conexão com a base de dados GM_biblioteca");
}

echo "<h2>🚀 Instalação da Biblioteca Ginestal Machado</h2>";
echo "<p>Base de dados: <strong>GM_biblioteca</strong></p>";

// Verificar tabelas existentes
$stmt = $conn->query("SHOW TABLES");
$tabelas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<h3>📋 Tabelas existentes:</h3>";
if (empty($tabelas_existentes)) {
    echo "<p>Nenhuma tabela encontrada.</p>";
} else {
    echo "<ul>";
    foreach ($tabelas_existentes as $tabela) {
        echo "<li>✅ $tabela</li>";
    }
    echo "</ul>";
}

// Tabelas necessárias
$tabelas_necessarias = [
    'autor' => "
        CREATE TABLE IF NOT EXISTS autor (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            biografia TEXT,
            data_nascimento DATE,
            nacionalidade VARCHAR(100),
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
    
    'editora' => "
        CREATE TABLE IF NOT EXISTS editora (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            endereco TEXT,
            telefone VARCHAR(20),
            email VARCHAR(255),
            website VARCHAR(255),
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
    
    'codigo_postal' => "
        CREATE TABLE IF NOT EXISTS codigo_postal (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(10) NOT NULL UNIQUE,
            localidade VARCHAR(255) NOT NULL,
            distrito VARCHAR(255),
            concelho VARCHAR(255),
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
    
    'genero' => "
        CREATE TABLE IF NOT EXISTS genero (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
    
    'utente' => "
        CREATE TABLE IF NOT EXISTS utente (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE,
            telefone VARCHAR(20),
            morada TEXT,
            codigo_postal_id INT,
            data_nascimento DATE,
            tipo_utente ENUM('aluno', 'professor', 'funcionario', 'externo') DEFAULT 'aluno',
            numero_utente VARCHAR(20) UNIQUE,
            data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ativo BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (codigo_postal_id) REFERENCES codigo_postal(id),
            INDEX idx_tipo_utente (tipo_utente),
            INDEX idx_numero_utente (numero_utente)
        )",
    
    'livro' => "
        CREATE TABLE IF NOT EXISTS livro (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(500) NOT NULL,
            autor_id INT NOT NULL,
            editora_id INT NOT NULL,
            genero_id INT NOT NULL,
            isbn VARCHAR(20) UNIQUE,
            edicao VARCHAR(50),
            ano_publicacao YEAR,
            numero_paginas INT,
            sinopse TEXT,
            quantidade_total INT DEFAULT 1,
            quantidade_disponivel INT DEFAULT 1,
            localizacao VARCHAR(100),
            estado ENUM('excelente', 'bom', 'regular', 'ruim') DEFAULT 'bom',
            data_aquisicao DATE,
            preco DECIMAL(10,2),
            observacoes TEXT,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (autor_id) REFERENCES autor(id) ON DELETE CASCADE,
            FOREIGN KEY (editora_id) REFERENCES editora(id) ON DELETE CASCADE,
            FOREIGN KEY (genero_id) REFERENCES genero(id) ON DELETE CASCADE,
            INDEX idx_titulo (titulo),
            INDEX idx_isbn (isbn),
            INDEX idx_autor (autor_id),
            INDEX idx_genero (genero_id),
            INDEX idx_disponivel (quantidade_disponivel)
        )",
    
    'requisicao' => "
        CREATE TABLE IF NOT EXISTS requisicao (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utente_id INT NOT NULL,
            livro_id INT NOT NULL,
            data_requisicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_devolucao_prevista DATE NOT NULL,
            data_devolucao_efetiva TIMESTAMP NULL,
            status ENUM('ativa', 'devolvida', 'atrasada', 'perdida') DEFAULT 'ativa',
            observacoes TEXT,
            multa DECIMAL(10,2) DEFAULT 0.00,
            renovacoes INT DEFAULT 0,
            FOREIGN KEY (utente_id) REFERENCES utente(id) ON DELETE CASCADE,
            FOREIGN KEY (livro_id) REFERENCES livro(id) ON DELETE CASCADE,
            INDEX idx_utente (utente_id),
            INDEX idx_livro (livro_id),
            INDEX idx_status (status),
            INDEX idx_data_devolucao (data_devolucao_prevista)
        )"
];

echo "<h3>🔧 Criando tabelas necessárias...</h3>";

$criadas = 0;
$erros = 0;

foreach ($tabelas_necessarias as $nome => $sql) {
    try {
        if (!in_array($nome, $tabelas_existentes)) {
            $conn->exec($sql);
            echo "<p>✅ Tabela <strong>$nome</strong> criada com sucesso!</p>";
            $criadas++;
        } else {
            echo "<p>ℹ️ Tabela <strong>$nome</strong> já existe.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Erro ao criar tabela <strong>$nome</strong>: " . $e->getMessage() . "</p>";
        $erros++;
    }
}

// Inserir dados iniciais se as tabelas estiverem vazias
echo "<h3>📚 Inserindo dados iniciais...</h3>";

$dados_iniciais = [
    'genero' => [
        "INSERT IGNORE INTO genero (nome, descricao) VALUES
        ('Ficção', 'Romances, contos e outras obras de ficção'),
        ('Não-Ficção', 'Biografias, ensaios, livros técnicos'),
        ('Ciências', 'Livros de física, química, biologia, matemática'),
        ('História', 'Livros sobre história mundial e nacional'),
        ('Literatura Portuguesa', 'Obras de autores portugueses'),
        ('Literatura Estrangeira', 'Obras traduzidas de autores estrangeiros'),
        ('Técnicos', 'Livros técnicos e profissionais'),
        ('Infantil', 'Livros para crianças e jovens'),
        ('Poesia', 'Coleções de poesia'),
        ('Dicionários', 'Dicionários e enciclopédias')"
    ],
    
    'codigo_postal' => [
        "INSERT IGNORE INTO codigo_postal (codigo, localidade, distrito, concelho) VALUES
        ('1000-001', 'Lisboa', 'Lisboa', 'Lisboa'),
        ('4000-001', 'Porto', 'Porto', 'Porto'),
        ('4700-001', 'Braga', 'Braga', 'Braga'),
        ('3800-001', 'Aveiro', 'Aveiro', 'Aveiro'),
        ('2400-001', 'Leiria', 'Leiria', 'Leiria')"
    ],
    
    'editora' => [
        "INSERT IGNORE INTO editora (nome, endereco, telefone, email, website) VALUES
        ('Porto Editora', 'Rua da Restauração, 365, 4099-025 Porto', '+351 225 191 700', 'info@portoeditora.pt', 'www.portoeditora.pt'),
        ('Leya', 'Rua Cidade de Córdova, 2, 2610-038 Amadora', '+351 214 472 600', 'info@leya.com', 'www.leya.com'),
        ('Bertrand', 'Rua Anchieta, 29, 1200-023 Lisboa', '+351 213 470 000', 'info@bertrand.pt', 'www.bertrand.pt')"
    ],
    
    'autor' => [
        "INSERT IGNORE INTO autor (nome, biografia, data_nascimento, nacionalidade) VALUES
        ('José Saramago', 'Escritor português, Nobel da Literatura em 1998', '1922-11-16', 'Portuguesa'),
        ('Fernando Pessoa', 'Poeta e escritor português, figura central do modernismo', '1888-06-13', 'Portuguesa'),
        ('Eça de Queirós', 'Romancista português do século XIX', '1845-11-25', 'Portuguesa')"
    ]
];

foreach ($dados_iniciais as $tabela => $sql_array) {
    if (in_array($tabela, $tabelas_existentes)) {
        try {
            foreach ($sql_array as $sql) {
                $conn->exec($sql);
            }
            echo "<p>✅ Dados iniciais inseridos na tabela <strong>$tabela</strong></p>";
        } catch (PDOException $e) {
            echo "<p>ℹ️ Dados iniciais da tabela <strong>$tabela</strong> já existem ou erro: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<h3>🎯 Testando o sistema...</h3>";

// Testar estatísticas
$estatisticas = $db->getEstatisticas();
echo "<p>📊 Estatísticas carregadas:</p>";
echo "<ul>";
echo "<li>Total de livros: " . $estatisticas['total_livros'] . "</li>";
echo "<li>Total de utentes: " . $estatisticas['total_utentes'] . "</li>";
echo "<li>Requisições ativas: " . $estatisticas['emprestimos_ativos'] . "</li>";
echo "<li>Em atraso: " . $estatisticas['emprestimos_atrasados'] . "</li>";
echo "</ul>";

echo "<h3>✅ Instalação concluída!</h3>";
echo "<p><strong>Resumo:</strong></p>";
echo "<ul>";
echo "<li>Tabelas criadas: $criadas</li>";
echo "<li>Erros: $erros</li>";
echo "<li>Sistema: Pronto para uso</li>";
echo "</ul>";

echo "<p><a href='index.php' class='btn btn-primary'>🚀 Acessar Sistema</a></p>";
echo "<p><a href='debug_database.php' class='btn btn-secondary'>🔍 Ver Estrutura Completa</a></p>";

$db->closeConnection();
?>
