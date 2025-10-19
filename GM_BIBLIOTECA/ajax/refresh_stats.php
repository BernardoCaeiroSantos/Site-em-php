<?php
/**
 * Arquivo AJAX para atualizar estatísticas da biblioteca
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    require_once '../config/database.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        $estatisticas = $db->getEstatisticas();
        
        echo json_encode([
            'success' => true,
            'stats' => $estatisticas,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro de conexão com a base de dados'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
