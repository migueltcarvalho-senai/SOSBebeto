// Lógica de ações e AJAX do Aluno - SOSBebeto
let googleUser = null;
let tipoSelecionado = '';

// Inicia configurações do Google Sign in Client ID
window.onload = function() {
    google.accounts.id.initialize({
        client_id: GOOGLE_CLIENT_ID,
        callback: handleCredentialResponse
    });
    google.accounts.id.renderButton(
        document.getElementById("googleBtnContainer"),
        { theme: "outline", size: "large", text: "continue_with" }
    );
    google.accounts.id.prompt();
    
    // Inicia a execução periódica do polling do aluno imediatamente, atualiza a lista de 5 em 5s
    carregarFila();
    setInterval(carregarFila, 5000);
}

// Analisa e trata retorno obtido Google Client JWT
function handleCredentialResponse(response) {
    const payload = decodeJwtResponse(response.credential);
    googleUser = {
        name: payload.name,
        email: payload.email,
        picture: payload.picture
    };
    
    // Verificar se no Payload este aluno possui email oficial de Docente para redirecionar automático
    const formData = new FormData();
    formData.append('acao', 'verificar_email_docente');
    formData.append('email_google', googleUser.email);
    
    fetch('backend.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.docente === true) {
            window.location.href = 'professor.php'; // Encaminha o professor
        } else {
            // Entrar nas views do aluno configuradas e carregar visual verde logado
            document.getElementById('googleAuthBar').className = 'logado';
            document.getElementById('avisoLogin').style.display = 'none';
            document.getElementById('googleBtnContainer').style.display = 'none';
            document.getElementById('infoLogin').style.display = 'flex';
            
            document.getElementById('fotoPerfil').src = googleUser.picture;
            document.getElementById('emailPerfil').innerText = googleUser.email;
            document.getElementById('nomeAluno').value = googleUser.name;
        }
    });
}

function fazerLogoutGoogle() {
    google.accounts.id.disableAutoSelect(); // Remove pré auto-login em reload
    googleUser = null;
    location.reload(); // Recarrega tela pra matar sessão client
}

// Decodifica manual o Token da API Oauth Base64 JWT 
function decodeJwtResponse(token) {
    let base64Url = token.split('.')[1];
    let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    let jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
}

function abrirModal() {
    if (!googleUser) {
        alert("Autentique-se com o Google Institucional primeiro na aba superior verde para abrir um pedido.");
        return;
    }
    document.getElementById('modalAjuda').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modalAjuda').style.display = 'none';
    document.getElementById('descricaoProblema').value = '';
    document.getElementById('divDescricao').style.display = 'none';
    tipoSelecionado = '';
    document.querySelectorAll('.btn-tipo').forEach(btn => btn.classList.remove('ativo'));
}

window.onclick = function(event) {
    if (event.target === document.getElementById('modalAjuda')) {
        fecharModal();
    }
}

function selecionarTipo(tipo) {
    tipoSelecionado = tipo;
    // Reseta visibilidade dos botões ativados na tela pra 1
    document.querySelectorAll('.btn-tipo').forEach(btn => btn.classList.remove('ativo'));
    event.target.classList.add('ativo');
    
    // Condicionar visualização de descrição avulsa no 'Outros Problemas'
    if (tipo === 'outros') {
        document.getElementById('divDescricao').style.display = 'block';
        document.getElementById('descricaoProblema').focus();
    } else {
        document.getElementById('divDescricao').style.display = 'none';
        document.getElementById('descricaoProblema').value = '';
    }
}

function enviarPedido() {
    if (!googleUser) return;
    
    // Regulação forte para o usuário nao quebrar o POST do PHP
    if (!tipoSelecionado) {
        alert("Erro! Por favor selecione a premissa e tipo do seu problema.");
        return;
    }
    const descricao = document.getElementById('descricaoProblema').value;
    if (tipoSelecionado === 'outros' && !descricao.trim()) {
        alert("Atenção! Por favor descreva detalhadamente qual é o seu problema, pois o mesmo não está listado nos ícones fixos de falha.");
        return;
    }
    
    // Temporizador de 3s para evitar Multi-Click / Spam no PHP via POST FETCH
    const btnEnviar = document.getElementById('btnEnviar');
    btnEnviar.disabled = true;
    btnEnviar.innerText = "Enviando Requisição... Aguarde 3s de Trava do Sistema";
    
    const formData = new FormData();
    formData.append('acao', 'criar_chamada');
    formData.append('nome', googleUser.name);
    formData.append('email_google', googleUser.email);
    formData.append('tipo', tipoSelecionado);
    formData.append('descricao', descricao);
    
    fetch('backend.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            fecharModal();
            carregarFila();
        } else {
            alert("Erro durante inserção do SQL : " + data.erro);
        }
    })
    .finally(() => {
        setTimeout(() => {
            btnEnviar.disabled = false;
            btnEnviar.innerText = "Enviar Pedido de Ajuda a Fila";
        }, 3000);
    });
}

function carregarFila() {
    // Traz o HTML pronto e tratado em cores pela rotina interna das views PHP
    fetch('backend.php?acao=listar_ajudas_abertas')
    .then(res => res.text())
    .then(html => {
        document.getElementById('listaAjudas').innerHTML = html;
    });
}
