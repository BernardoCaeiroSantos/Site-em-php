<?php
<?php
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM editora");
    $stmt->execute();
    $editoras = $stmt->fetchAll();
    echo "<pre>";
    print_r($editoras);
    echo "</pre>";
} else {
    echo "Não foi possível ligar à base de dados.";
}
?>