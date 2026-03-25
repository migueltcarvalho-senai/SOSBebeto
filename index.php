<?php
// index.php - Portal inicial de entrada (Login e Redirecionamento) SOSBebeto
session_start();

// Verifica se o formulário de login foi ativado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_login'] ?? 'aluno';
    
    if ($tipo === 'aluno') {
        // Redireciona o aluno direto para a fila que o exigirá entrar no Google Identity Services
        header('Location: aluno.php');
        exit;
    } elseif ($tipo === 'professor') {
        $senha = $_POST['senha_professor'] ?? '';
        
        // Regra de negócios para admin master acessar como professor
        if ($senha === '123456789') {
            $_SESSION['prof_logado'] = true;
            header('Location: professor.php');
            exit;
        } else {
            $erro = "Senha incorreta para acesso de professor!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOSBebeto - Entrar no Sistema</title>
    <!-- Inclui o design interativo, CSS com cache buster timestamp! -->
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>
    <div class="login-container">
        <img src="image/robs.jpeg" class="logo-robs">
        <h2>Sistema de Fila de Ajuda</h2>
        <p>Acesse o SOSBebeto com a sua credencial abaixo:</p>
        
        <?php if (!empty($erro)): ?>
            <div class="erro-msg"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="radio-group">
                <label>
                    <input type="radio" name="tipo_login" value="aluno" id="radioAluno" onchange="toggleSenha()" <?= (!isset($erro)) ? 'checked' : '' ?>> Sou Aluno
                </label>
                <label>
                    <input type="radio" name="tipo_login" value="professor" id="radioProfessor" onchange="toggleSenha()" <?= (isset($erro)) ? 'checked' : '' ?>> Sou Professor
                </label>
            </div>
            
            <div id="divSenha" style="display: <?= isset($erro) ? 'block' : 'none' ?>;">
                <label for="senha_professor">Senha do Professor:</label>
                <input type="password" name="senha_professor" id="senha_professor" placeholder="******">
            </div>
            
            <button type="submit" class="btn btn-submit">Entrar no Painel</button>
        </form>
    </div>
    
    <!-- Script básico para trocar a exibição da senha  -->
    <script>
        function toggleSenha() {
            const isProf = document.getElementById('radioProfessor').checked;
            document.getElementById('divSenha').style.display = isProf ? 'block' : 'none';
        }
    </script>
    
    <!-- Engrenagem dos efeitos de design fundo/quadrados e interações hover -->
    <script src="assets/effects.js?v=<?= time() ?>"></script>
</body>
</html>
