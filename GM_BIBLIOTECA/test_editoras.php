<?php
/**
 * Teste da página de editoras
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("❌ Erro de conexão com a base de dados");
}

echo "<h2>🧪 Teste - Página de Editoras</h2>";

// Testar se conseguimos acessar a tabela
try {
    $sql = "SELECT * FROM editora LIMIT 5";
    $stmt = $conn->query($sql);
    $editoras = $stmt->fetchAll();
    
    echo "<p>✅ Query executada com sucesso!</p>";
    echo "<p>📊 Encontradas " . count($editoras) . " editoras</p>";
    
    if (!empty($editoras)) {
        echo "<h3>📋 Exemplos de editoras:</h3>";
        // A tabela foi removida daqui para não poluir a saída.
    }
    
    echo "<hr>";
    echo "<h3>🔗 Teste a página agora:</h3>";
    echo "<p><a href='pages/editoras.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📚 Ir para Editoras</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro na query: " . $e->getMessage() . "</p>";
}

$db->closeConnection();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
a:hover { opacity: 0.8; }
</style>