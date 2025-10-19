<?php
/**
 * Configuração da Base de Dados - Biblioteca Ginestal Machado
 */

class Database {
    private $host = "localhost";
    private $db_name = "gm_biblioteca";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    public function tabelaExiste($nomeTabela) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$nomeTabela]);
        return $stmt->rowCount() > 0;
    }

    public function getEstatisticas() {
        $conn = $this->getConnection();
        $estatisticas = [];
        
        try {
            // Total de livros
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM livro");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $estatisticas['total_livros'] = $result['total'] ?? 0;
            
            // Total de utentes
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM utente");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $estatisticas['total_utentes'] = $result['total'] ?? 0;
            
            // Empréstimos ativos (requisições onde re_data_devolucao é uma data futura)
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM requisicao WHERE re_data_devolucao > CURDATE()");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $estatisticas['emprestimos_ativos'] = $result['total'] ?? 0;
            
            // Empréstimos atrasados (requisições onde re_data_devolucao é uma data passada)
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM requisicao WHERE re_data_devolucao < CURDATE()");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $estatisticas['emprestimos_atrasados'] = $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            // Se houver erro, retorna valores padrão
            $estatisticas = [
                'total_livros' => 0,
                'total_utentes' => 0,
                'emprestimos_ativos' => 0,
                'emprestimos_atrasados' => 0
            ];
        }
        
        return $estatisticas;
    }
}

$db = new Database();
