<?php
// professor.php - Visualização central do professor do SOSBebeto com listas accordion
session_start();

// Barreira de proteção - Nega qualquer acesso direto caso a sessão não corresponda ao Admin
if (!isset($_SESSION['prof_logado']) || $_SESSION['prof_logado'] !== true) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOSBebeto - Painel do Professor</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>
    <div class="painel-container">
        <div class="topo-painel">
            <h2>Gestão da Fila de Ajudas (Painel Administrativo)</h2>
            <div class="filtros-professor">
                <label class="brutalist-toggle">
                    <input type="checkbox" id="toggleOrdem" onchange="alternarOrdem()">
                    <span>Mais Antiga Primeiro no Topo</span>
                </label>
                <label class="brutalist-toggle">
                    <input type="checkbox" id="toggleOcultar" onchange="alternarOcultar()">
                    <span>Ocultar Atendimentos Finalizadas</span>
                </label>
                <a href="?sair=1" class="btn btn-voltar">Encerrar Sessão Segura</a>
            </div>
        </div>
        
        <p class="aviso">Clique na caixa respectiva do aluno para expandir informações, ler o conteúdo e definir a ação apropriada de feedback para ele.</p>
        
        <div id="listaChamadas">
            <p>Carregando as requisições ativas da turma...</p>
        </div>
    </div>

    <script src="assets/professor.js?v=<?= time() ?>"></script>
    <script src="assets/effects.js?v=<?= time() ?>"></script>
</body>
</html>
