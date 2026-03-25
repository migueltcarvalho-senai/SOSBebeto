<?php
// backend.php - Servidor API que coordena as chamadas AJAX do frontend
session_start();
require_once 'config.php';

// Captura a ação vinda do frontend (GET ou POST)
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// ==========================================
// AÇÃO 1: CRIAR NOVA CHAMADA DE AJUDA (Aluno)
// ==========================================
if ($acao === 'criar_chamada') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $email_google = trim($_POST['email_google'] ?? '');

    // Verifica envio de dados essenciais
    if (empty($email_google) || empty($nome) || empty($tipo)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos, por favor refaça a operação.']);
        exit;
    }

    // Regra: descrição só é enviada ao banco de dados se o tipo for "outros"
    if ($tipo !== 'outros') {
        $descricao = null;
    }

    try {
        // Insere os dados como um pedido 'Em andamento' no SOSBebeto 
        $stmt = $pdo->prepare("INSERT INTO ajuda (nome_aluno, email_google, descricao, tipo, hora, status_ajuda) VALUES (?, ?, ?, ?, NOW(), 'Em andamento')");
        $stmt->execute([$nome, $email_google, $descricao, $tipo]);
        echo json_encode(['sucesso' => true]);
    }
    catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// AÇÃO 2: MUDAR STATUS DA CHAMADA (Professor)
// ==========================================
if ($acao === 'mudar_status') {
    // Garante controle de acesso restrito apenas ao professor logado
    if (!isset($_SESSION['prof_logado']) || $_SESSION['prof_logado'] !== true) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso não autorizado ao painel.']);
        exit;
    }

    $id = $_POST['id'] ?? 0;
    $status_novo = $_POST['status_novo'] ?? '';

    try {
        if ($status_novo === 'excluir') {
            // Exclui a mensagem permanentemente do SOSBebeto
            $stmt = $pdo->prepare("DELETE FROM ajuda WHERE id = ?");
            $stmt->execute([$id]);
        }
        else {
            // Converte ação de botão em status real no MySQL
            $status_banco = '';
            if ($status_novo === 'aceitar')
                $status_banco = 'em atendimento'; // Correcao ENUM
            if ($status_novo === 'finalizar')
                $status_banco = 'encerramento';
            if ($status_novo === 'reabrir')
                $status_banco = 'Em andamento'; // Correcao ENUM

            if ($status_banco) {
                $stmt = $pdo->prepare("UPDATE ajuda SET status_ajuda = ? WHERE id = ?");
                $stmt->execute([$status_banco, $id]);
            }
        }
        echo json_encode(['sucesso' => true]);
    }
    catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro na ação do professor: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// AÇÃO 3: LISTAR AJUDAS PARA O ALUNO (Polling)
// ==========================================
if ($acao === 'listar_ajudas_abertas') {
    try {
        // Alunos só veem pedidos que não estão encerrados. (Listagem principal)
        $stmt = $pdo->prepare("SELECT * FROM ajuda WHERE LOWER(status_ajuda) != 'encerramento' ORDER BY hora DESC");
        $stmt->execute();
        $chamadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($chamadas)) {
            // Mensagem simples em português do brasil quando fila está vazia
            echo "<p class='aviso'>Nenhum pedido na fila. Turma tranquila! 😎</p>";
            exit;
        }

        foreach ($chamadas as $c) {
            // Formatar os tempos e badges (etiquetas de cores) do SOSBebeto
            $classe_status = str_replace(' ', '-', mb_strtolower($c['status_ajuda'], 'UTF-8'));
            $hora_formatada = date('H:i - d/m', strtotime($c['hora']));
            $tipo = htmlspecialchars($c['tipo']);
            $nome = htmlspecialchars($c['nome_aluno']);
            $status = htmlspecialchars($c['status_ajuda']);

            // Renderiza o visual de blocos do aluno com os dados dinâmicos do banco
            echo "<div class='ajuda-item'>
                    <strong>{$nome}</strong> pediu ajuda.
                    <small>Tipo: {$tipo}</small>
                    <span class='badge {$classe_status}'>{$status}</span>
                    <span>{$hora_formatada}</span>
                  </div>";
        }
    }
    catch (Exception $e) {
        echo "<p class='erro'>Ocorreu um erro ao consultar o banco de dados.</p>";
    }
    exit;
}

// ==================================================
// AÇÃO 4: LISTAR CHAMADAS DO PROFESSOR (Accordion)
// ==================================================
if ($acao === 'listar_ajudas_professor') {
    $ocultar = $_GET['ocultar_finalizadas'] ?? '0';
    $ordem = strtolower($_GET['ordem'] ?? 'desc');
    $ordem_sql = $ordem === 'asc' ? 'ASC' : 'DESC';

    try {
        // [1] Carregar os itens em atendimento da vez pro topo (destaque)
        $stmtEmAnalise = $pdo->prepare("SELECT * FROM ajuda WHERE LOWER(status_ajuda) = 'em atendimento' ORDER BY hora $ordem_sql");
        $stmtEmAnalise->execute();
        $emAnalise = $stmtEmAnalise->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($emAnalise)) {
            echo "<div class='em-atendimento-destaque'>
                    <h3><div class='pulse-dot'></div> Alunos em Atendimento</h3>";
            foreach ($emAnalise as $c) {
                $id = $c['id'];
                $nome = htmlspecialchars($c['nome_aluno']);
                $tipo = htmlspecialchars($c['tipo']);
                $hora_formatada = date('H:i', strtotime($c['hora']));
                echo "<div class='chamada-destaque'>
                        <div class='info-destaque'>
                            <strong>{$nome}</strong> - <u>{$tipo}</u> <span class='hora-badge'>{$hora_formatada}</span>
                        </div>
                        <button class='btn btn-finalizar btn-pequeno' onclick='acaoChamada({$id}, \"finalizar\")'>🏁 Finalizar</button>
                      </div>";
            }
            echo "</div>";
        }

        // [2] Processar restrição de filtros (Esconder ou mostrar os alunos encerrados)
        $where = "";
        if ($ocultar === '1') {
            $where = "WHERE LOWER(status_ajuda) != 'encerramento'";
        }

        // [3] Consultar tudo (em andamento [novo] aparece em primeiro sempre no SOSBebeto) 
        $sql = "SELECT * FROM ajuda $where ORDER BY 
                CASE LOWER(status_ajuda) 
                    WHEN 'em andamento' THEN 1 
                    WHEN 'em atendimento' THEN 2 
                    WHEN 'encerramento' THEN 3 
                    ELSE 4 
                END, hora $ordem_sql";

        $stmt = $pdo->query($sql);
        $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($todas)) {
            echo "<p class='aviso'>Lista vazia.</p>";
            exit;
        }

        // [4] Exibir chamadas expansíveis estilo sanfona
        foreach ($todas as $c) {
            $id = $c['id'];
            $nome = htmlspecialchars($c['nome_aluno']);
            $email = htmlspecialchars($c['email_google']);
            $tipo = htmlspecialchars($c['tipo']);
            $descricao = $c['descricao'] ? htmlspecialchars($c['descricao']) : "Sem descrição extra enviada.";
            $status = htmlspecialchars($c['status_ajuda']);
            $hora_formatada = date('H:i - d/m', strtotime($c['hora']));

            // Garantir as classes corretas com a tag do status para pintar o badge no CSS
            $status_lower_para_css = mb_strtolower($c['status_ajuda'], 'UTF-8');
            $status_lower_para_css = str_replace('á', 'a', $status_lower_para_css); // Normalizar
            $classe_status = str_replace(' ', '-', $status_lower_para_css);

            echo "<div class='chamada' id='chamada-{$id}' data-status_ocupado=''>
                    <div class='cabecalho-chamada' onclick='expandirChamada({$id})'>
                        <div class='info-principal'>
                            <strong>{$nome}</strong> ({$email}) pediu ajuda: <u>{$tipo}</u>
                        </div>
                        <div class='info-secundaria'>
                            <span class='hora'>{$hora_formatada}</span>
                            <span class='badge {$classe_status}'>{$status}</span>
                        </div>
                    </div>
                    
                    <div class='detalhes-chamada'>
                        <p><strong>Descrição informada pelo aluno:</strong></p>
                        <p>{$descricao}</p>
                        <div class='acoes'>";

            $stat_lower = strtolower(trim($c['status_ajuda']));
            // Remover acentos para garantir comparação segura
            $stat_lower = preg_replace('/[áàãâä]/u', 'a', $stat_lower);
            $stat_lower = preg_replace('/[éèêë]/u', 'e', $stat_lower);
            $stat_lower = preg_replace('/[íìîï]/u', 'i', $stat_lower);
            $stat_lower = preg_replace('/[óòõôö]/u', 'o', $stat_lower);
            $stat_lower = preg_replace('/[úùûü]/u', 'u', $stat_lower);
            $stat_lower = preg_replace('/[ç]/u', 'c', $stat_lower);

            // Renderiza apenas botões condizentes ao status lógico do Aluno-Professor
            if ($stat_lower === 'em andamento') {
                echo "<button class='btn btn-excluir' onclick='acaoChamada({$id}, \"excluir\")'>🗑️ Excluir</button>
                      <button class='btn btn-aceitar' onclick='acaoChamada({$id}, \"aceitar\")'>✅ Aceitar Aluno</button>";
            }
            elseif ($stat_lower === 'em atendimento') {
                echo "<button class='btn btn-finalizar' onclick='acaoChamada({$id}, \"finalizar\")'>🏁 Finalizar Atendimento</button>";
            }
            elseif ($stat_lower === 'encerramento') {
                echo "<button class='btn btn-excluir' onclick='acaoChamada({$id}, \"excluir\")'>🗑️ Limpar do Histórico</button>
                      <button class='btn btn-reabrir' onclick='acaoChamada({$id}, \"reabrir\")'>🔄 Reabrir Chamada</button>";
            }

            echo "      </div>
                    </div>
                  </div>";
        }
    }
    catch (Exception $e) {
        echo "<p class='erro'>Erro ao carregar lista. Detalhe técnico: " . $e->getMessage() . "</p>";
    }
    exit;
}

// ==================================================
// AÇÃO 5: VERIFICAR LIGAÇÃO DO EMAIL E REDIRECIONAR 
// ==================================================
if ($acao === 'verificar_email_docente') {
    $email = trim($_POST['email_google'] ?? '');

    // Se o remetente for do corpo docente senai, faça a transição pro modelo professor
    if (strpos($email, '@docente.senai.br') !== false) {
        $_SESSION['prof_logado'] = true;
        $_SESSION['email_docente'] = $email;
        echo json_encode(['docente' => true]);
    }
    else {
        echo json_encode(['docente' => false]);
    }
    exit;
}
?>
