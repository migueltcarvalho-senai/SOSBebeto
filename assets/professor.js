// Lógica de ações e AJAX do Professor - SOSBebeto

function carregarChamadas() {
    // Analisa os botões brutalistas para a filtragem principal
    const ocultar = document.getElementById('toggleOcultar').checked ? '1' : '0';
    // Asc ou Desc por regra do Professor
    const ordem = document.getElementById('toggleOrdem').checked ? 'asc' : 'desc';
    
    fetch(`backend.php?acao=listar_ajudas_professor&ocultar_finalizadas=${ocultar}&ordem=${ordem}`)
    .then(res => res.text())
    .then(html => {
        const container = document.getElementById('listaChamadas');
        
        // Bloqueio de Segurança Crítico:
        // Só atualiza os nodes do HTML se o professor não estiver com as "Ações" do accordion de um card expostas (status ocupado ativo)
        if (container.dataset.status_ocupado !== 'true') {
            container.innerHTML = html;
        }
    });
}

// Configura Polling principal a cada 5 segundos buscando blocos processados HTML no servidor
setInterval(carregarChamadas, 5000);
document.addEventListener("DOMContentLoaded", carregarChamadas);

// Executadores diretos no toggle para fluidez e atualizar os blocos instataneamente de tela
function alternarOcultar() { carregarChamadas(); }
function alternarOrdem() { carregarChamadas(); }

// Controles interativos em Accordion HTML/CSS com Toggle Expande Cards
function expandirChamada(id) {
    const container = document.getElementById('listaChamadas');
    const chamada = document.getElementById('chamada-' + id);
    
    if (chamada.classList.contains('expandido')) {
        // Encerra e destrava leitura da api para refresh
        chamada.classList.remove('expandido');
        container.dataset.status_ocupado = '';
    } else {
        // Retrai todos do layout por precaução caso já haja outro aberto
        document.querySelectorAll('.chamada').forEach(c => c.classList.remove('expandido'));
        chamada.classList.add('expandido'); // Libera foco ao card
        // Trava de Auto-Update p/ Professor para que ele consiga Ler sem a tela pular / fechar sozinha
        container.dataset.status_ocupado = 'true';
    }
}

// Envia comandos dos botões de aprovar/alteração da tabela/card final pro status
function acaoChamada(id, acao) {
    const btn = event.target;
    // Retenção do status para re-habilitar posterior caso quebre no PHP BD 
    const textoOriginal = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = "Processando Comando de Carga...";
    
    const formData = new FormData();
    formData.append('acao', 'mudar_status');
    formData.append('id', id);
    formData.append('status_novo', acao);
    
    fetch('backend.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            // Destrava painel de segurança do update timer e forca Refresh para pegar o card sumido na nova prioridade da engine
            document.getElementById('listaChamadas').dataset.status_ocupado = '';
            carregarChamadas();
        } else {
            alert("Aviso: Falha no controle de comandos de DB - " + data.erro);
            btn.disabled = false;
            btn.innerText = textoOriginal;
        }
    });
}
