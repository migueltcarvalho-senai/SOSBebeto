// Efeitos visuais SOSBebeto - Tema Star Wars "Dark Empire"
// Estrelas como no filme: densas, brilhantes, algumas maiores com aura

document.addEventListener('DOMContentLoaded', () => {
    // 1. Cria o container do fundo animado
    const bgContainer = document.createElement('div');
    bgContainer.className = 'bg-animation';
    document.body.prepend(bgContainer);

    // Gera uma estrela individual no estilo Star Wars
    function createStar() {
        const star = document.createElement('div');
        star.className = 'animated-square';

        // Tres categorias de estrela - como nos filmes: pequenas, medias e gigantes
        const tipo = Math.random();
        let size, glowIntensity;

        if (tipo < 0.60) {
            // 60% sao estrelas pequenas (pontos de luz no fundo)
            size = Math.random() * 2 + 1;             // 1px a 3px
            glowIntensity = 'low';
        } else if (tipo < 0.88) {
            // 28% sao estrelas medias (visiveis e brilhantes)
            size = Math.random() * 3 + 3;             // 3px a 6px
            glowIntensity = 'medium';
        } else {
            // 12% sao estrelas grandes (estrelas proeminentes)
            size = Math.random() * 4 + 6;             // 6px a 10px
            glowIntensity = 'high';
        }

        star.style.width = size + 'px';
        star.style.height = size + 'px';

        // Posicao X aleatoria em toda a largura da tela
        const startX = Math.random() * window.innerWidth;
        star.style.left = startX + 'px';

        // Velocidade variada: estrelas maiores se movem mais devagar (efeito de profundidade)
        const duration = glowIntensity === 'high'
            ? Math.random() * 6 + 18       // Grandes: 18s a 24s (lentas - estao mais perto)
            : glowIntensity === 'medium'
                ? Math.random() * 8 + 12   // Medias: 12s a 20s
                : Math.random() * 10 + 8;  // Pequenas: 8s a 18s (rapidas - estao longe)

        star.style.animationDuration = duration + 's';

        // Cor e brilho de acordo com o tamanho
        const isRed = Math.random() > 0.85; // 15% levemente avermelhadas

        if (isRed) {
            // Estrelas avermelhadas - gigantes vermelhas, raras e dramaticas
            star.style.background = 'rgba(255, 100, 100, 0.95)';
            star.style.boxShadow = `
                0 0 ${size * 2}px rgba(255, 80, 80, 0.9),
                0 0 ${size * 5}px rgba(200, 0, 0, 0.5),
                0 0 ${size * 10}px rgba(150, 0, 0, 0.2)
            `;
        } else if (glowIntensity === 'high') {
            // Estrelas grandes brancas com aura impactante
            star.style.background = 'rgba(255, 255, 255, 1)';
            star.style.boxShadow = `
                0 0 ${size * 2}px rgba(255, 255, 255, 1),
                0 0 ${size * 5}px rgba(255, 255, 255, 0.7),
                0 0 ${size * 12}px rgba(200, 220, 255, 0.4)
            `;
        } else if (glowIntensity === 'medium') {
            // Estrelas medias com brilho moderado
            star.style.background = 'rgba(255, 255, 255, 0.95)';
            star.style.boxShadow = `
                0 0 ${size * 1.5}px rgba(255, 255, 255, 0.9),
                0 0 ${size * 4}px rgba(220, 230, 255, 0.4)
            `;
        } else {
            // Estrelas pequenas - discretas
            star.style.background = 'rgba(255, 255, 255, 0.8)';
            star.style.boxShadow = `0 0 ${size}px rgba(255, 255, 255, 0.6)`;
        }

        bgContainer.appendChild(star);

        // Remove da memoria quando a animacao terminar
        setTimeout(() => {
            star.remove();
        }, duration * 1000);
    }

    // Preenche a tela de imediato com um campo estelar denso
    // Mais estrelas iniciais para efeito imersivo do Star Wars
    for (let i = 0; i < 120; i++) {
        // Escalonamento aleatorio para nao aparecer todas de uma vez
        setTimeout(createStar, Math.random() * 4000);
    }

    // Continua gerando estrelas continuamente para repor as que saem
    setInterval(createStar, 250); // Nova estrela a cada 250ms = campo denso
});

// 2. Efeito Ripple nos Botoes - onda branca ao clicar
// Delegacao global no body para pegar botoes dynamicos via fetch tambem
document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn') || e.target.closest('button');
    if (btn) {
        // Pega as dimensoes do botao para calcular o circulo do ripple
        const rect = btn.getBoundingClientRect();
        const circle = document.createElement('span');
        const diameter = Math.max(btn.clientWidth, btn.clientHeight);
        const radius = diameter / 2;

        // Posiciona o circulo exatamente onde o usuario clicou
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${e.clientX - rect.left - radius}px`;
        circle.style.top = `${e.clientY - rect.top - radius}px`;
        circle.classList.add('ripple');

        // Garante que o overflow do botao esta cortado para o efeito funcionar
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';

        btn.appendChild(circle);

        // Remove o elemento depois que a animacao termina (0.7s)
        setTimeout(() => {
            circle.remove();
        }, 700);
    }
});
