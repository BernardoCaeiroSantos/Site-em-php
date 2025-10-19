<?php
/**
 * Script de diagnóstico para códigos postais
 */

echo "<h2>🔍 Diagnóstico - Códigos Postais</h2>";

try {
    require_once 'config/database.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        echo "<p>❌ Erro de conexão com a base de dados</p>";
        exit;
    }
    
    echo "<p>✅ Conexão com base de dados estabelecida</p>";
    echo "<p>Base de dados: <strong>GM_biblioteca</strong></p>";
    
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'codigo_postal'");
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "<p>✅ Tabela 'codigo_postal' EXISTE</p>";
        
        // Mostrar estrutura da tabela
        $stmt = $conn->query("DESCRIBE codigo_postal");
        $colunas = $stmt->fetchAll();
        
        echo "<h3>📋 Estrutura da tabela:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $conn->query("SELECT COUNT(*) as total FROM codigo_postal");
        $total = $stmt->fetch()['total'];
        echo "<p>📊 Total de registros: <strong>$total</strong></p>";
        
        // Mostrar alguns registros
        $stmt = $conn->query("SELECT * FROM codigo_postal LIMIT 5");
        $registros = $stmt->fetchAll();
        
        if (!empty($registros)) {
            echo "<h3>📋 Primeiros 5 registros:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Código Postal</th><th>Localidade</th></tr>";
            
            foreach ($registros as $reg) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($reg['cod_postal'] ?? $reg['codigo'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($reg['cod_localidade'] ?? $reg['localidade'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p>❌ Tabela 'codigo_postal' NÃO EXISTE</p>";
        
        // Tentar criar a tabela
        echo "<p>🔧 Tentando criar a tabela...</p>";
        
        $sql = "CREATE TABLE codigo_postal (
            cod_postal VARCHAR(10) NOT NULL PRIMARY KEY,
            cod_localidade VARCHAR(255) NOT NULL
        )";
        
        $conn->exec($sql);
        echo "<p>✅ Tabela criada com sucesso!</p>";
        
        // Inserir dados de exemplo
        $exemplos = [
            ['1000-001', 'Lisboa'],
            ['4000-001', 'Porto'],
            ['4700-001', 'Braga']
        ];
        
        $stmt = $conn->prepare("INSERT INTO codigo_postal (cod_postal, cod_localidade) VALUES (?, ?)");
        
        foreach ($exemplos as $exemplo) {
            $stmt->execute($exemplo);
        }
        
        echo "<p>✅ Dados de exemplo inseridos!</p>";
    }
    
    echo "<hr>";
    echo "<h3>🔗 Links para testar:</h3>";
    echo "<p><a href='pages/codigos_postais.php'>📮 Página de Códigos Postais</a></p>";
    echo "<p><a href='index.php'>🏠 Página Principal</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro PDO: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
p { margin: 10px 0; }
table { border-collapse: collapse; margin: 10px 0; background: white; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
