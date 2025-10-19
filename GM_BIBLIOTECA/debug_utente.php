
<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>🔍 Estrutura da tabela UTENTE</h2>";

try {
    $stmt = $conn->query("DESCRIBE utente");
    $colunas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>" . $coluna['Field'] . "</strong></td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . ($coluna['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar alguns registros
    $stmt = $conn->query("SELECT * FROM utente LIMIT 3");
    $registros = $stmt->fetchAll();
    
    if (!empty($registros)) {
        echo "<h3>📋 Primeiros registros:</h3>";
        echo "<pre>";
        print_r($registros);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
