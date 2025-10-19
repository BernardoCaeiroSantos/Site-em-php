<?php
/**
 * Página de teste para adicionar autores
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("❌ Erro de conexão com a base de dados");
}

echo "<h2>🧪 Teste - Adicionar Autores</h2>";
echo "<p>Base de dados: <strong>GM_biblioteca</strong></p>";

// Verificar se a tabela autor existe
if ($db->tabelaExiste('autor')) {
    echo "<p>✅ Tabela 'autor' existe</p>";
    
    // Contar autores existentes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM autor");
    $total = $stmt->fetch()['total'];
    echo "<p>📊 Total de autores: <strong>$total</strong></p>";
    
    // Listar autores existentes
    $stmt = $conn->query("SELECT id, nome, nacionalidade FROM autor ORDER BY nome");
    $autores = $stmt->fetchAll();
    
    if (!empty($autores)) {
        echo "<h3>📚 Autores existentes:</h3>";
        echo "<ul>";
        foreach ($autores as $autor) {
            echo "<li><strong>" . htmlspecialchars($autor['nome']) . "</strong>";
            if ($autor['nacionalidade']) {
                echo " - " . htmlspecialchars($autor['nacionalidade']);
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
} else {
    echo "<p>❌ Tabela 'autor' não existe</p>";
    echo "<p>Execute o instalador: <a href='install.php'>install.php</a></p>";
}

echo "<hr>";
echo "<h3>🔗 Links úteis:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>🏠 Página Principal</a></li>";
echo "<li><a href='pages/autores.php'>👤 Página de Autores</a></li>";
echo "<li><a href='install.php'>🔧 Instalador</a></li>";
echo "</ul>";

$db->closeConnection();
?>
