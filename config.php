<?php
// Arquivo de configuração de conexão ao banco de dados SOSBebeto

$host = '127.0.0.1';
$port = '3307';
$dbname = 'sosbebetodb';
$user = 'root';
$pass = '';

try {
    // Cria a conexão via PDO e define os caracteres para UTF-8 brasileiro padrão
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Configura o PDO para lançar exceções genéricas em caso de erro, facilitando debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configura o fuso horário direto na sessão do MySQL (Brasília, Brasil)
    $pdo->exec("SET time_zone = '-03:00'");
    
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados. O banco sosbebetodb ou MySQL podem estar offline: " . $e->getMessage());
}
?>
