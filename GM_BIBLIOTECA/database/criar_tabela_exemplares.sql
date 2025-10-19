
-- Tabela de Exemplares de Livros
CREATE TABLE IF NOT EXISTS livro_exemplar (
    lex_cod INT AUTO_INCREMENT PRIMARY KEY,
    lex_li_cod INT NOT NULL,
    lex_numero VARCHAR(20) NOT NULL UNIQUE,
    lex_estado ENUM('disponivel', 'emprestado', 'manutencao', 'perdido', 'danificado') DEFAULT 'disponivel',
    lex_localizacao VARCHAR(100),
    lex_observacoes TEXT,
    lex_data_aquisicao DATE,
    lex_data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lex_data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lex_li_cod) REFERENCES livro(li_cod) ON DELETE CASCADE,
    INDEX idx_livro (lex_li_cod),
    INDEX idx_estado (lex_estado),
    INDEX idx_numero (lex_numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir exemplares para os livros existentes
INSERT INTO livro_exemplar (lex_li_cod, lex_numero, lex_estado, lex_localizacao, lex_data_aquisicao)
SELECT 
    li_cod,
    CONCAT('EX', LPAD(li_cod, 4, '0'), '-', LPAD(@row_num := @row_num + 1, 3, '0')) as lex_numero,
    'disponivel' as lex_estado,
    li_localizacao as lex_localizacao,
    li_data_aquisicao as lex_data_aquisicao
FROM livro, (SELECT @row_num := 0) r
WHERE li_qtd_total > 0;
  