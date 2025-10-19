-- Script para criar a tabela codigo_postal com a estrutura simplificada
-- Execute este script na sua base de dados GM_biblioteca

USE GM_biblioteca;

-- Criar tabela codigo_postal se não existir
CREATE TABLE IF NOT EXISTS codigo_postal (
    cod_postal VARCHAR(10) NOT NULL PRIMARY KEY,
    cod_localidade VARCHAR(255) NOT NULL
);

-- Inserir alguns dados de exemplo
INSERT IGNORE INTO codigo_postal (cod_postal, cod_localidade) VALUES
('1000-001', 'Lisboa'),
('1000-002', 'Lisboa'),
('1000-003', 'Lisboa'),
('4000-001', 'Porto'),
('4000-002', 'Porto'),
('4700-001', 'Braga'),
('3800-001', 'Aveiro'),
('2400-001', 'Leiria'),
('3000-001', 'Coimbra'),
('2900-001', 'Setúbal');

-- Verificar se a tabela foi criada
SELECT 'Tabela codigo_postal criada com sucesso!' as status;
SELECT COUNT(*) as total_registros FROM codigo_postal;
