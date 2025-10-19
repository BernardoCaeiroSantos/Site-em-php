
<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Corrigindo estrutura da tabela requisicao...</h2>";

try {
    // Verificar estrutura atual
    echo "<h3>Estrutura atual:</h3>";
    $stmt = $conn->query("SHOW CREATE TABLE requisicao");
    $result = $stmt->fetch();
    echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>";
    
    // Remover constraint problemática se existir
    try {
        $conn->exec("ALTER TABLE requisicao DROP FOREIGN KEY fk_req_exemplar");
        echo "<p style='color: green;'>✓ Constraint fk_req_exemplar removida</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ Constraint fk_req_exemplar não existe ou já foi removida</p>";
    }
    
    // Verificar se coluna re_li_cod existe
    $stmt = $conn->query("SHOW COLUMNS FROM requisicao LIKE 're_li_cod'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE requisicao ADD COLUMN re_li_cod INT AFTER re_ut_cod");
        echo "<p style='color: green;'>✓ Coluna re_li_cod adicionada</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Coluna re_li_cod já existe</p>";
    }
    
    // Adicionar foreign key correta
    try {
        $conn->exec("ALTER TABLE requisicao 
                     ADD CONSTRAINT fk_req_livro 
                     FOREIGN KEY (re_li_cod) REFERENCES livro(li_cod) 
                     ON UPDATE CASCADE 
                     ON DELETE RESTRICT");
        echo "<p style='color: green;'>✓ Foreign key fk_req_livro adicionada</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ Foreign key já existe: " . $e->getMessage() . "</p>";
    }
    
    // Mostrar estrutura final
    echo "<h3>Estrutura final:</h3>";
    $stmt = $conn->query("DESCRIBE requisicao");
    $columns = $stmt->fetchAll();
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✓ Correção concluída com sucesso!</h3>";
    echo "<p><a href='pages/requisicoes.php'>Ir para Requisições</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}
?>
