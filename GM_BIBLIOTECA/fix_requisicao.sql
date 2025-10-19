
-- Primeiro, vamos ver a estrutura atual
SHOW CREATE TABLE requisicao;

-- Remover a constraint problemática
ALTER TABLE requisicao DROP FOREIGN KEY fk_req_exemplar;

-- Adicionar a coluna re_li_cod se não existir
ALTER TABLE requisicao ADD COLUMN IF NOT EXISTS re_li_cod INT;

-- Adicionar a foreign key correta para livro
ALTER TABLE requisicao 
ADD CONSTRAINT fk_req_livro 
FOREIGN KEY (re_li_cod) REFERENCES livro(li_cod) 
ON UPDATE CASCADE 
ON DELETE RESTRICT;

-- Verificar a estrutura final
DESCRIBE requisicao;
