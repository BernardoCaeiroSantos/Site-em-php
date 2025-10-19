<?php
/**
 * Teste da página de códigos postais
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("❌ Erro de conexão com a base de dados");
}

echo "<h2>🧪 Teste - Página de Códigos Postais</h2>";

// Testar se conseguimos acessar a tabela
try {
    $sql = "SELECT * FROM codigo_postal ORDER BY cod_localidade, cod_postal LIMIT 5";
    $stmt = $conn->query($sql);
    $codigos = $stmt->fetchAll();
    
    echo "<p>✅ Query executada com sucesso!</p>";
    echo "<p>📊 Encontrados " . count($codigos) . " códigos postais</p>";
    
    if (!empty($codigos)) {
        echo "<h3>📋 Exemplos de códigos postais:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Código Postal</th><th>Localidade</th></tr>";
        
        foreach ($codigos as $codigo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($codigo['cod_postal']) . "</td>";
            echo "<td>" . htmlspecialchars($codigo['cod_localidade']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>🔗 Teste a página agora:</h3>";
    echo "<p><a href='pages/codigos_postais.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📮 Ir para Códigos Postais</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro na query: " . $e->getMessage() . "</p>";
}

$db->closeConnection();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
a:hover { opacity: 0.8; }
</style>
