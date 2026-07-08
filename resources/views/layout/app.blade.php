<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF6D00">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
    <style>
        .navbar { animation: slideDown .4s cubic-bezier(.34,1.56,.64,1) both; }
        .page-transition { animation: pageEnter .5s cubic-bezier(.34,1.56,.64,1) both; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pageEnter {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>

@include('layout.sidebar')

<div class="app-shell">
    <header class="topbar">
        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Abrir menu">
            <span></span><span></span><span></span>
        </button>
        <div class="topbar-right">
            <button class="search-toggle-btn" id="searchToggleBtn" onclick="toggleSearch()" aria-label="Buscar">
                <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
            </button>

            <div class="topbar-user">
                <span>👋 {{ session('user_name') }}</span>
            </div>
        </div>
    </header>

    <div class="container page-transition">
        @yield('content')
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-open');
        document.getElementById('sidebarBackdrop').classList.toggle('active');
        document.getElementById('hamburgerBtn').classList.toggle('open');
    }

    function toggleSearch() {
        const wrapper = document.getElementById('topbarSearch');
        const input = document.getElementById('topSearchInput');
        const toggleBtn = document.getElementById('searchToggleBtn');
        const isOpen = wrapper.classList.toggle('search-open');
        toggleBtn.classList.toggle('active', isOpen);
        if (isOpen) {
            setTimeout(() => input.focus(), 150);
        } else {
            input.value = '';
            const evt = new Event('input');
            input.dispatchEvent(evt);
        }
    }

    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('topbarSearch');
        if (wrapper.classList.contains('search-open') && !wrapper.contains(e.target)) {
            wrapper.classList.remove('search-open');
            document.getElementById('searchToggleBtn').classList.remove('active');
        }
    });

    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('topbarSearch');
        if (wrapper.classList.contains('search-open') && !wrapper.contains(e.target)) {
            wrapper.classList.remove('search-open');
        }
    });
</script>

{{-- MODAL SOBRE — CAROUSEL --}}
<div id="sobreModal" class="modal-overlay" onclick="closeSobreModal()">
    <div class="sobre-modal-box" onclick="event.stopPropagation()">

        {{-- Slide 1: Desenvolvedor --}}
        <div class="sobre-slide active" id="sobre-slide-0">
            <div class="sobre-visual">
                <div class="sobre-visual-icon">👨‍💻</div>
                <span class="sobre-visual-label">Desenvolvedor</span>
            </div>
            <div class="sobre-info-card">
                <button class="sobre-close" onclick="closeSobreModal()">✕</button>
                <span class="sobre-tag">Criador do Sistema</span>
                <h2 class="sobre-name">Markos Samuell</h2>
                <p class="sobre-role">Desenvolvedor Full Stack & IoT</p>
                <p class="sobre-desc">
                    Estudante de Desenvolvimento Web e IoT no SENAI-CTTI-MG,
                    apaixonado por criar sistemas práticos e funcionais que
                    resolvem problemas reais do dia a dia.
                </p>
                <div class="sobre-social">
                    <a href="https://github.com/MarkossCod" target="_blank" class="sobre-social-btn" title="GitHub">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.82-.26.82-.58v-2.03c-3.34.73-4.04-1.61-4.04-1.61-.54-1.38-1.33-1.75-1.33-1.75-1.09-.74.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49 1 .11-.78.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 3-.4c1.02 0 2.04.13 3 .4 2.28-1.55 3.29-1.23 3.29-1.23.66 1.66.25 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.83.58C20.56 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                    </a>
                    <a href="https://www.linkedin.com/in/markos-samuell" target="_blank" class="sobre-social-btn" title="LinkedIn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Slide 2: Instituição --}}
        <div class="sobre-slide" id="sobre-slide-1">
            <div class="sobre-visual" style="background: linear-gradient(145deg, #BF360C, #e64a19);">
                <div class="sobre-visual-icon">🏫</div>
                <span class="sobre-visual-label">Instituição</span>
            </div>
            <div class="sobre-info-card">
                <button class="sobre-close" onclick="closeSobreModal()">✕</button>
                <span class="sobre-tag">Ensino Técnico</span>
                <h2 class="sobre-name">SENAI-CTTI-MG</h2>
                <p class="sobre-role">Centro de Tecnologia da Informação</p>
                <p class="sobre-desc">
                    O SENAI-CTTI-MG forma profissionais qualificados em tecnologia
                    da informação, com foco em desenvolvimento web, redes e IoT,
                    preparando os alunos para o mercado de trabalho com excelência.
                </p>
                <div class="sobre-social">
                    <a href="https://www.fiemg.com.br/unidades/senai-centro-de-treinamento-da-tecnologia-da-informacao-ctti" target="_blank" class="sobre-social-btn" title="Site SENAI">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Slide 3: Ferramentas --}}
        <div class="sobre-slide" id="sobre-slide-2">
            <div class="sobre-visual" style="background: linear-gradient(145deg, #E65100, #FF6D00);">
                <div class="sobre-visual-icon">🛠️</div>
                <span class="sobre-visual-label">Tecnologias</span>
            </div>
            <div class="sobre-info-card">
                <button class="sobre-close" onclick="closeSobreModal()">✕</button>
                <span class="sobre-tag">Stack Utilizada</span>
                <h2 class="sobre-name">Laravel + Vite</h2>
                <p class="sobre-role">Backend PHP & Frontend Moderno</p>
                <p class="sobre-desc">
                    Sistema construído com Laravel para o backend, Vite para
                    build dos assets, banco de dados SQLite e hospedagem em
                    ambiente containerizado.
                </p>
                <div class="sobre-social">
                    <a href="https://laravel.com" target="_blank" class="sobre-social-btn" title="Laravel">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Navegação --}}
        <div class="sobre-nav">
            <button class="sobre-nav-btn" onclick="sobreAnterior()" aria-label="Anterior">&#8249;</button>
            <div class="sobre-dots">
                <button class="sobre-dot active" onclick="sobreGoTo(0)" aria-label="Slide 1"></button>
                <button class="sobre-dot" onclick="sobreGoTo(1)" aria-label="Slide 2"></button>
                <button class="sobre-dot" onclick="sobreGoTo(2)" aria-label="Slide 3"></button>
            </div>
            <button class="sobre-nav-btn" onclick="sobreProximo()" aria-label="Próximo">&#8250;</button>
        </div>

    </div>
</div>

{{-- MODAL MODELOS DE TEXTO --}}
<div id="modelosModal" class="modal-overlay" onclick="closeModal('modelosModal')">
    <div class="modal-box" style="max-width:680px;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>📝 Modelos de Texto</h2>
            <button class="modal-close" onclick="closeModal('modelosModal')">✕</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px;" id="modelosList"></div>
    </div>
</div>

<script>
    const modelos = [
        { titulo: "Contato Inicial", texto: `Olá, boa tarde\nTudo bem\nSou Markos da Cabtec\nEstou entrando em contato referente ao chamado ....` },
        { titulo: "Impressora Reparada", texto: `Boa tarde,\n\nImpressora-N/S:..... reparada, alocada e adicionada no estoque G3-ANDAR01\n\nAtenciosamente,` },
        { titulo: "Equipamento Backup Reparado", texto: `Bom dia,\n\nEquipamento backup reparado e testado, colocado no estoque.\n\n@Diane Santos da Cabtec by Kyubi,\nGentileza realizar alteração no controle de backups.\n\nAtenciosamente,` },
        { titulo: "Solicitação OS/SA", texto: `@wallace\nSegue solicitação;\n\nOS: \nSA: \nN/S: \nPEÇAS: \nREPOR: \nMAU USO: \n\nAtenciosamente,` },
        { titulo: "Emitir NF Backup", texto: `Bom dia,\n\n@Rodrigo Alves da Cabtec by Kyubi,\nFavor emitir a NF do backup:\n\nCHAMADO: \nNS: \nPedido: \n\nAtenciosamente,` },
        { titulo: "Agendamento Técnico", texto: `Boa tarde,\n\n@Kauare,\nOS: \n\nDados necessários para realizar o agendamento técnico.\n\nEndereço: \nNome Usuário: \nTelefone/Ramal: \nSetor: \nHorário de Atendimento: \nNúmero de Série: \nModelo: \nProblema/Solicitação:\nPrecisa de backup: \n\nData: \nTécnico: \nObservação: \n\nAtenciosamente,` },
        { titulo: "Emitir NF Equipamentos Reparados", texto: `Bom dia,\n\n@Rodrigo Alves da Cabtec by Kyubi,\nFavor emitir a NF dos equipamentos reparados:\n\nChamado: \nSN: \nNF: \n\nAtenciosamente,` },
        { titulo: "Cotação de Peça", texto: `Bom dia,\n\n@Clarisse Furtado da Cabtec by Kyubi,\nGentileza cotar o item e prazo para entrega:\nPN: \nDESCRIÇÃO: \nCÓD. TOTVS: \n\n@Wallace Carvalho Silva da Cabtec by Kyubi,\nGentileza cadastrar valores.\n\nAtenciosamente,` },
        { titulo: "Centro de Reparos - Tela Quebrada", texto: `Bom dia,\n\n@Ana Leão da Cabtec by Kyubi,\nEquipamento irá para o centro de reparos para reparo da tela quebrada.\nSN: \nOrçamento: \n\nSegue em anexo foto para laudo.\n\nAtenciosamente,` },
        { titulo: "Nota de Saída Backup", texto: `Boa tarde,\n\n@Rodrigo Alves da Cabtec by Kyubi,\n@Chamados,\nFavor gerar nota de saída para remessa de backup.\nNúmero do pedido: \n\nAtenciosamente,` },
        { titulo: "Coletor Backup Revisado", texto: `Boa tarde,\n\n@Ana\nColetor backup revisado e em bom estado.\nAlocado no estoque.\n\nAtenciosamente,` },
        { titulo: "Sucatear Equipamento", texto: `Bom dia!\n\n@Rodrigo Alves da Cabtec by Kyubi,\nGentileza sucatear equipamento, pois não será viável o reparo.\n\n@Wallace Carvalho Silva da Cabtec by Kyubi,\nFavor dar baixa no serial do estoque.\nChamado: \n\nAtenciosamente,` },
        { titulo: "Equipamentos Recebidos", texto: `Boa tarde,\n\n@Rodrigo Alves da Cabtec by Kyubi,\nInformo que os equipamentos foram recebidos conforme o previsto.\nIniciando o processo de configuração dos devidos equipamentos.\n\nAtenciosamente,` },
        { titulo: "Laudo e Orçamento", texto: `Boa tarde,\n\n@Kauare dos Santos da Cabtec by Kyubi,\nSegue anexo laudo e orçamento.\n\nOrçamento: \nNS: \n\nAtenciosamente,` },
        { titulo: "Equipamento Testado e Alocado", texto: `Boa tarde,\n\n@Ana Leão da Cabtec by Kyubi,\nEquipamento testado e alocado no estoque.\n\nAtenciosamente,` },
        { titulo: "NF Equipamento Reparado", texto: `Boa tarde,\n\n@Rodrigo Alves da Cabtec by Kyubi,\nFavor emitir NF do equipamento reparado:\n\nChamado: \nNS: \nNF: \n\nAtenciosamente,` },
        { titulo: "Cotação e Cadastro de Peça", texto: `Boa tarde,\n\n@Wallace Carvalho Silva da Cabtec by Kyubi,\nFavor realizar a cotação e o cadastro do valor referente ao item abaixo:\n\nPN: \nDescrição: \n\n@Lucas Abreu da Cabtec by Kyubi | @Rodrigo Alves da Cabtec by Kyubi,\nFavor cadastrar a peça.\n\nAtenciosamente,` },
    ];

    function openModelosModal() {
        const list = document.getElementById('modelosList');
        if (list.innerHTML === '') {
            modelos.forEach((m, i) => {
                list.innerHTML += `
                <div style="background:#fafafa;border-radius:12px;padding:16px;border-left:4px solid #ff7b00;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span style="font-weight:700;font-size:14px;color:#1a1a1a;">📋 ${m.titulo}</span>
                        <button onclick="copiarModelo(${i})" id="btn-modelo-${i}"
                            style="background:#ff7b00;color:white;border:none;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;margin-top:0;width:auto;">
                            Copiar
                        </button>
                    </div>
                    <pre style="font-size:12px;color:#555;white-space:pre-wrap;line-height:1.6;font-family:inherit;">${m.texto}</pre>
                </div>`;
            });
        }
        document.getElementById('modelosModal').classList.add('modal-active');
    }

    function copiarModelo(i) {
        navigator.clipboard.writeText(modelos[i].texto).then(() => {
            const btn = document.getElementById(`btn-modelo-${i}`);
            btn.textContent = '✅ Copiado!';
            btn.style.background = '#4caf50';
            setTimeout(() => {
                btn.textContent = 'Copiar';
                btn.style.background = '#ff7b00';
            }, 2000);
        });
    }

    let sobreSlideAtual = 0;
    const sobreTotalSlides = 3;

    function sobreGoTo(index) {
        document.getElementById('sobre-slide-' + sobreSlideAtual).classList.remove('active');
        document.querySelectorAll('.sobre-dot')[sobreSlideAtual].classList.remove('active');
        sobreSlideAtual = index;
        document.getElementById('sobre-slide-' + sobreSlideAtual).classList.add('active');
        document.querySelectorAll('.sobre-dot')[sobreSlideAtual].classList.add('active');
    }

    function sobreProximo() {
        sobreGoTo((sobreSlideAtual + 1) % sobreTotalSlides);
    }

    function sobreAnterior() {
        sobreGoTo((sobreSlideAtual - 1 + sobreTotalSlides) % sobreTotalSlides);
    }

    function openSobreModal() {
        sobreGoTo(0);
        document.getElementById('sobreModal').classList.add('modal-active');
    }

    function closeSobreModal() {
        document.getElementById('sobreModal').classList.remove('modal-active');
    }

    function openInfoModal() {
        document.getElementById('infoModal').classList.add('modal-active');
    }

    function openViewModal(title, content) {
        document.getElementById('viewModalTitle').innerText = title || '';
        document.getElementById('viewModalContent').innerText = content || '';
        document.getElementById('viewModal').classList.add('modal-active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('modal-active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSobreModal();
            closeModal('modelosModal');
            if(document.getElementById('infoModal')) closeModal('infoModal');
            if(document.getElementById('viewModal')) closeModal('viewModal');
        }
    });
</script>
@yield('modals')
</body>
</html>