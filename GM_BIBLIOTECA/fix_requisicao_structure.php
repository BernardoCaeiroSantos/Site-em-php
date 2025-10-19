
<?php
/**
 * Script para corrigir a estrutura da tabela requisicao
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>🔧 Corrigindo estrutura da tabela requisicao</h2>";

try {
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'requisicao'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela requisicao não existe!</p>";
        echo "<p>Execute o instalador primeiro: <a href='install.php'>install.php</a></p>";
        exit;
    }
    
    // Mostrar estrutura atual
    echo "<h3>📋 Estrutura atual da tabela:</h3>";
    $stmt = $conn->query("DESCRIBE requisicao");
    $colunas = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
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
    
    // Verificar se precisa adicionar colunas
    $colunas_necessarias = [
        're_data_devolucao_prevista' => "ALTER TABLE requisicao ADD COLUMN re_data_devolucao_prevista DATE NULL AFTER re_data_requisicao",
        're_data_devolucao_efetiva' => "ALTER TABLE requisicao ADD COLUMN re_data_devolucao_efetiva DATETIME NULL AFTER re_data_devolucao_prevista",
        're_status' => "ALTER TABLE requisicao ADD COLUMN re_status ENUM('ativa', 'devolvida', 'atrasada', 'perdida') DEFAULT 'ativa' AFTER re_data_devolucao_efetiva"
    ];
    
    echo "<h3>🔄 Verificando e adicionando colunas necessárias:</h3>";
    
    foreach ($colunas_necessarias as $coluna => $sql) {
        $existe = false;
        foreach ($colunas as $col) {
            if ($col['Field'] == $coluna) {
                $existe = true;
                break;
            }
        }
        
        if (!$existe) {
            try {
                $conn->exec($sql);
                echo "<p style='color: green;'>✅ Coluna '$coluna' adicionada com sucesso!</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao adicionar coluna '$coluna': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Coluna '$coluna' já existe.</p>";
        }
    }
    
    // Atualizar registros existentes
    echo "<h3>🔄 Atualizando registros existentes:</h3>";
    
    // Adicionar data de devolução prevista para requisições sem ela
    $sql = "UPDATE requisicao 
            SET re_data_devolucao_prevista = DATE_ADD(re_data_requisicao, INTERVAL 15 DAY) 
            WHERE re_data_devolucao_prevista IS NULL";
    $stmt = $conn->exec($sql);
    echo "<p style='color: green;'>✅ $stmt registro(s) atualizado(s) com data de devolução prevista.</p>";
    
    // Atualizar status das requisições
    $sql = "UPDATE requisicao 
            SET re_status = CASE 
                WHEN re_data_devolucao_efetiva IS NOT NULL THEN 'devolvida'
                WHEN DATEDIFF(re_data_devolucao_prevista, CURDATE()) < 0 THEN 'atrasada'
                ELSE 'ativa'
            END
            WHERE re_status IS NULL OR re_status = ''";
    $stmt = $conn->exec($sql);
    echo "<p style='color: green;'>✅ $stmt registro(s) atualizado(s) com status.</p>";
    
    echo "<h3>✅ Estrutura corrigida com sucesso!</h3>";
    echo "<p><a href='pages/requisicoes.php'>➡️ Ir para Requisições</a></p>";
    echo "<p><a href='index.php'>🏠 Voltar ao Início</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

$db->closeConnection();
?>