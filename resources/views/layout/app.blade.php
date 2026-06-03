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

<nav class="navbar">
    <div class="logo">NOTESSYTEM</div>
    <ul>
        <li>
            <a href="{{ secure_url(route('notes.index', [], false)) }}">
                <span>📋</span> Gerenciar Notas
            </a>
        </li>
        <li>
            <a href="{{ secure_url(route('notes.create', [], false)) }}">
                <span>➕</span> Nova Nota
            </a>
        </li>
        <li>
            <a href="#" onclick="openModelosModal(); return false;">
                <span>📝</span> Modelos
            </a>
        </li>
        <li>
            <a href="#" onclick="openSobreModal(); return false;">
                <span>👤</span> Sobre
            </a>
        </li>
        <li>
            <span style="color:rgba(255,255,255,0.8); font-size:13px; padding:8px 10px; display:flex; align-items:center; gap:6px;">
                👋 {{ session('user_name') }}
            </span>
        </li>
        <li>
            <form action="{{ secure_url(route('logout', [], false)) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:white; padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; margin-top:0; width:auto;">
                    Sair
                </button>
            </form>
        </li>
    </ul>
</nav>

<div class="container page-transition">
    @yield('content')
</div>

{{-- MODAL SOBRE --}}
<div id="sobreModal" class="modal-overlay" onclick="closeSobreModal()">
    <div class="modal-box sobre-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>👤 Sobre o Sistema</h2>
            <button class="modal-close" onclick="closeSobreModal()">✕</button>
        </div>
        <div class="sobre-content">
            <div class="sobre-item">
                <span class="sobre-icon">🧑‍💻</span>
                <div>
                    <p class="sobre-label">Desenvolvedor</p>
                    <p class="sobre-value">Markos Samuell</p>
                </div>
            </div>
            <div class="sobre-item">
                <span class="sobre-icon">🏫</span>
                <div>
                    <p class="sobre-label">Instituição de Ensino</p>
                    <p class="sobre-value">SENAI-CTTI-MG</p>
                </div>
            </div>
            <div class="sobre-item">
                <span class="sobre-icon">💡</span>
                <div>
                    <p class="sobre-label">Motivo da Criação</p>
                    <p class="sobre-value">Sistema desenvolvido para organizar e gerenciar chamados de forma prática, permitindo criar notas e dividi-las em seções para melhor controle das atividades.</p>
                </div>
            </div>
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

    function openSobreModal() {
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