-- Base de dados para Biblioteca Ginestal Machado
-- Criado em: 2024

CREATE DATABASE IF NOT EXISTS GM_biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE GM_biblioteca;

-- Tabela de Autores
CREATE TABLE autor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    biografia TEXT,
    data_nascimento DATE,
    nacionalidade VARCHAR(100),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Editoras
CREATE TABLE editora (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    endereco TEXT,
    telefone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Códigos Postais
CREATE TABLE codigo_postal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    localidade VARCHAR(255) NOT NULL,
    distrito VARCHAR(255),
    concelho VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Gêneros
CREATE TABLE genero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Utentes (Usuários)
CREATE TABLE utente (
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
);

-- Tabela de Livros
CREATE TABLE livro (
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
);

-- Tabela de Requisições (Empréstimos)
CREATE TABLE requisicao (
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
);

-- Inserir dados iniciais

-- Gêneros
INSERT INTO genero (nome, descricao) VALUES
('Ficção', 'Romances, contos e outras obras de ficção'),
('Não-Ficção', 'Biografias, ensaios, livros técnicos'),
('Ciências', 'Livros de física, química, biologia, matemática'),
('História', 'Livros sobre história mundial e nacional'),
('Literatura Portuguesa', 'Obras de autores portugueses'),
('Literatura Estrangeira', 'Obras traduzidas de autores estrangeiros'),
('Técnicos', 'Livros técnicos e profissionais'),
('Infantil', 'Livros para crianças e jovens'),
('Poesia', 'Coleções de poesia'),
('Dicionários', 'Dicionários e enciclopédias');

-- Códigos Postais (exemplos)
INSERT INTO codigo_postal (codigo, localidade, distrito, concelho) VALUES
('1000-001', 'Lisboa', 'Lisboa', 'Lisboa'),
('4000-001', 'Porto', 'Porto', 'Porto'),
('4700-001', 'Braga', 'Braga', 'Braga'),
('3800-001', 'Aveiro', 'Aveiro', 'Aveiro'),
('2400-001', 'Leiria', 'Leiria', 'Leiria');

-- Editoras
INSERT INTO editora (nome, endereco, telefone, email, website) VALUES
('Porto Editora', 'Rua da Restauração, 365, 4099-025 Porto', '+351 225 191 700', 'info@portoeditora.pt', 'www.portoeditora.pt'),
('Leya', 'Rua Cidade de Córdova, 2, 2610-038 Amadora', '+351 214 472 600', 'info@leya.com', 'www.leya.com'),
('Bertrand', 'Rua Anchieta, 29, 1200-023 Lisboa', '+351 213 470 000', 'info@bertrand.pt', 'www.bertrand.pt'),
('Presença', 'Rua Anchieta, 29, 1200-023 Lisboa', '+351 213 470 000', 'info@presenca.pt', 'www.presenca.pt'),
('Dom Quixote', 'Rua Anchieta, 29, 1200-023 Lisboa', '+351 213 470 000', 'info@domquixote.pt', 'www.domquixote.pt');

-- Autores
INSERT INTO autor (nome, biografia, data_nascimento, nacionalidade) VALUES
('José Saramago', 'Escritor português, Nobel da Literatura em 1998', '1922-11-16', 'Portuguesa'),
('Fernando Pessoa', 'Poeta e escritor português, figura central do modernismo', '1888-06-13', 'Portuguesa'),
('Eça de Queirós', 'Romancista português do século XIX', '1845-11-25', 'Portuguesa'),
('Sophia de Mello Breyner', 'Poetisa e escritora portuguesa', '1919-11-06', 'Portuguesa'),
('António Lobo Antunes', 'Romancista português contemporâneo', '1942-09-01', 'Portuguesa');

-- Utentes de exemplo
INSERT INTO utente (nome, email, telefone, morada, codigo_postal_id, data_nascimento, tipo_utente, numero_utente) VALUES
('João Silva', 'joao.silva@ginestal.pt', '912345678', 'Rua das Flores, 123', 1, '2005-03-15', 'aluno', 'UT001'),
('Maria Santos', 'maria.santos@ginestal.pt', '923456789', 'Avenida Central, 456', 2, '2000-07-22', 'professor', 'UT002'),
('Pedro Costa', 'pedro.costa@ginestal.pt', '934567890', 'Praça da República, 789', 3, '1998-12-10', 'funcionario', 'UT003'),
('Ana Rodrigues', 'ana.rodrigues@ginestal.pt', '945678901', 'Rua da Escola, 321', 4, '2003-05-18', 'aluno', 'UT004'),
('Carlos Ferreira', 'carlos.ferreira@ginestal.pt', '956789012', 'Largo da Igreja, 654', 5, '1995-09-30', 'professor', 'UT005');

-- Livros de exemplo
INSERT INTO livro (titulo, autor_id, editora_id, genero_id, isbn, edicao, ano_publicacao, numero_paginas, sinopse, quantidade_total, quantidade_disponivel, localizacao, estado, data_aquisicao, preco) VALUES
('Memorial do Convento', 1, 1, 1, '978-972-0-04555-4', '1ª Edição', 1982, 352, 'Romance histórico sobre a construção do Convento de Mafra', 3, 3, 'Estante A-1', 'bom', '2023-01-15', 15.90),
('Livro do Desassossego', 2, 2, 6, '978-972-0-04123-4', 'Edição Crítica', 1982, 480, 'Obra fundamental de Fernando Pessoa', 2, 2, 'Estante B-2', 'excelente', '2023-02-20', 22.50),
('Os Maias', 3, 3, 6, '978-972-0-04125-8', 'Edição Anotada', 1888, 672, 'Romance realista português', 4, 4, 'Estante A-3', 'bom', '2023-03-10', 18.75),
('O Cavaleiro da Dinamarca', 4, 4, 8, '978-972-0-04126-5', 'Edição Ilustrada', 1964, 96, 'Conto para jovens sobre viagens medievais', 5, 5, 'Estante C-1', 'bom', '2023-04-05', 12.00),
('Memória de Elefante', 5, 5, 1, '978-972-0-04127-2', '1ª Edição', 1979, 240, 'Primeiro romance de António Lobo Antunes', 2, 2, 'Estante B-1', 'regular', '2023-05-12', 16.80);

-- Requisições de exemplo
INSERT INTO requisicao (utente_id, livro_id, data_devolucao_prevista, status, observacoes) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'ativa', 'Empréstimo normal'),
(2, 3, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'ativa', 'Empréstimo para professor'),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'atrasada', 'Devolução em atraso'),
(4, 4, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'ativa', 'Empréstimo para aluno'),
(5, 5, CURDATE(), 'devolvida', 'Devolvido no prazo');

-- Criar views úteis
CREATE VIEW v_livros_disponiveis AS
SELECT 
    l.id,
    l.titulo,
    a.nome as autor,
    e.nome as editora,
    g.nome as genero,
    l.isbn,
    l.ano_publicacao,
    l.quantidade_disponivel,
    l.localizacao
FROM livro l
LEFT JOIN autor a ON l.autor_id = a.id
LEFT JOIN editora e ON l.editora_id = e.id
LEFT JOIN genero g ON l.genero_id = g.id;

CREATE VIEW v_requisicoes_ativas AS
SELECT 
    r.id,
    u.nome as utente,
    l.titulo as livro,
    a.nome as autor,
    r.data_requisicao,
    r.data_devolucao_prevista,
    r.status,
    DATEDIFF(r.data_devolucao_prevista, CURDATE()) as dias_restantes
FROM requisicao r
JOIN utente u ON r.utente_id = u.id
JOIN livro l ON r.livro_id = l.id
JOIN autor a ON l.autor_id = a.id
WHERE r.status = 'ativa';

-- Triggers para atualizar quantidade disponível
DELIMITER $

CREATE TRIGGER tr_requisicao_insert
AFTER INSERT ON requisicao
FOR EACH ROW
BEGIN
    UPDATE livro 
    SET quantidade_disponivel = quantidade_disponivel - 1 
    WHERE id = NEW.livro_id;
END$

CREATE TRIGGER tr_requisicao_update
AFTER UPDATE ON requisicao
FOR EACH ROW
BEGIN
    IF OLD.status != 'devolvida' AND NEW.status = 'devolvida' THEN
        UPDATE livro 
        SET quantidade_disponivel = quantidade_disponivel + 1 
        WHERE id = NEW.livro_id;
    END IF;
END$

DELIMITER ;
