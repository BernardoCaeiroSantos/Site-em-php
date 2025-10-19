<?php
/**
 * Script simples para criar a tabela codigo_postal
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("❌ Erro de conexão com a base de dados GM_biblioteca");
}

echo "<h2>🔧 Configuração da Tabela codigo_postal</h2>";
echo "<p>Base de dados: <strong>GM_biblioteca</strong></p>";

try {
    // Criar a tabela codigo_postal
    $sql = "CREATE TABLE IF NOT EXISTS codigo_postal (
        cod_postal VARCHAR(10) NOT NULL PRIMARY KEY,
        cod_localidade VARCHAR(255) NOT NULL
    )";
    
    $conn->exec($sql);
    echo "<p>✅ Tabela 'codigo_postal' criada com sucesso!</p>";
    
    // Inserir dados de exemplo
    $exemplos = [
        ['1000-001', 'Lisboa'],
        ['1000-002', 'Lisboa'],
        ['4000-001', 'Porto'],
        ['4000-002', 'Porto'],
        ['4700-001', 'Braga'],
        ['3800-001', 'Aveiro'],
        ['2400-001', 'Leiria'],
        ['3000-001', 'Coimbra'],
        ['2900-001', 'Setúbal'],
        ['8000-001', 'Faro']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO codigo_postal (cod_postal, cod_localidade) VALUES (?, ?)");
    
    $inseridos = 0;
    foreach ($exemplos as $exemplo) {
        if ($stmt->execute($exemplo)) {
            $inseridos++;
        }
    }
    
    echo "<p>✅ $inseridos códigos postais de exemplo inseridos!</p>";
    
    // Verificar o resultado
    $stmt = $conn->query("SELECT COUNT(*) as total FROM codigo_postal");
    $total = $stmt->fetch()['total'];
    echo "<p>📊 Total de códigos postais na tabela: <strong>$total</strong></p>";
    
    // Mostrar alguns exemplos
    $stmt = $conn->query("SELECT * FROM codigo_postal ORDER BY cod_localidade, cod_postal LIMIT 10");
    $codigos = $stmt->fetchAll();
    
    echo "<h3>📋 Exemplos de códigos postais:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Código Postal</th><th>Localidade</th></tr>";
    
    foreach ($codigos as $codigo) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($codigo['cod_postal']) . "</td>";
        echo "<td>" . htmlspecialchars($codigo['cod_localidade']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🎉 Configuração Concluída!</h3>";
    echo "<p>Agora pode acessar a página de códigos postais:</p>";
    echo "<p><a href='pages/codigos_postais.php' class='btn btn-primary'>📮 Gerenciar Códigos Postais</a></p>";
    echo "<p><a href='index.php' class='btn btn-secondary'>🏠 Voltar ao Início</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se a base de dados 'GM_biblioteca' existe e se tem permissões para criar tabelas.</p>";
}

$db->closeConnection();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    margin: 5px; 
    text-decoration: none; 
    border-radius: 5px; 
}
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
