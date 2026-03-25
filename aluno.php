<?php
// aluno.php - Painel do aluno do SOSBebeto
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOSBebeto - Painel do Aluno</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <!-- SDK do Google Identity Services para autenticação de alunos sem senha -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div id="googleAuthBar" class="nao-logado">
        <div id="infoLogin" style="display:none; align-items: center; gap: 10px;">
            <img id="fotoPerfil" src="" alt="Foto" width="30" height="30" style="border-radius:50%">
            <span id="emailPerfil"></span>
            <button onclick="fazerLogoutGoogle()" class="btn btn-voltar google-btn">Sair / Trocar Conta</button>
        </div>
        <div id="avisoLogin">
            Você precisa entrar com sua conta Google Institucional
        </div>
        <div id="googleBtnContainer"></div>
    </div>

    <div class="painel-container">
        <div class="topo-painel">
            <h2>Fila de Ajuda - Alunos</h2>
            <div style="display:flex; gap: 10px; align-items:center;">
                <button class="btn" onclick="abrirModal()">+ Criar Nova Ajuda</button>
                <a href="index.php" class="btn btn-voltar">Sair para Menu Inicial</a>
            </div>
        </div>

        <div id="listaAjudas">
            <p>Carregando fila atual em andamento...</p>
        </div>
    </div>

    <!-- Modal Responsivo de Criação de Pedido de Ajuda -->
    <div id="modalAjuda" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Novo Pedido de Ajuda ao Professor</h3>
                <span class="close-btn" onclick="fecharModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <label>Seu Nome Automático (Google SignIn):</label>
                <input type="text" id="nomeAluno" readonly>
                
                <label>Selecione a categoria do seu problema atual:</label>
                <div class="botoes-tipo">
                    <button class="btn-tipo" onclick="selecionarTipo('Programação de atividades')">Programação de Atividades</button>
                    <button class="btn-tipo" onclick="selecionarTipo('mau funcionamento do computador')">Mau Funcionamento de Hardware</button>
                    <button class="btn-tipo" onclick="selecionarTipo('problemas com os programas do computador')">Problemas com Programas</button>
                    <button class="btn-tipo" onclick="selecionarTipo('ajuda com os projetos')">Ajuda com Projetos</button>
                    <button class="btn-tipo" style="grid-column: span 2;" onclick="selecionarTipo('outros')">Outros Problemas / Dúvidas Específicas</button>
                </div>
                
                <div id="divDescricao" style="display: none; margin-top: 15px;">
                    <label>Descreva com mais detalhes o seu problema para o professor:</label>
                    <textarea id="descricaoProblema" rows="3"></textarea>
                </div>
                
                <button id="btnEnviar" class="btn btn-submit" style="margin-top:20px;" onclick="enviarPedido()">Enviar Pedido de Ajuda para a Fila</button>
            </div>
        </div>
    </div>

    <script>
        // Token JWT Client ID de permissão da Conta Google vinculado ao usuário
        const GOOGLE_CLIENT_ID = '1094491385956-fm9f59dmu9ps7g4b1j5qhe4r9bad5egd.apps.googleusercontent.com';
    </script>
    <script src="assets/aluno.js?v=<?= time() ?>"></script>
    <script src="assets/effects.js?v=<?= time() ?>"></script>
</body>
</html>
