<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ../index.php');
    exit;
}

require __DIR__ . '/../process/conexao.php';
$usuarioId = (int) $_SESSION['usuario']['id'];
$produtos = [];

if ($conexao) {
    $stmt = $conexao->prepare('SELECT id, nome, estoque_fisico FROM produtos WHERE usuario_id = :id ORDER BY nome ASC');
    $stmt->execute([':id' => $usuarioId]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas</title>
    <link rel="stylesheet" href="../estilo/entregas.css">
</head>
<body>
<?php include __DIR__ . '/menu.php'; ?>

<main class="entregas-page">
    <header class="entregas-topo">
        <div>
            <p class="eyebrow">Logística</p>
            <h1>Gestão de entregas</h1>
        </div>
    </header>

    <section class="cards-grid">
        <article class="painel painel-formulario">
            <h2>Nova entrega</h2>

            <form id="form-entrega" class="form-entrega">
                <div class="campo">
                    <label for="pedido_id">Pedido</label>
                    <input type="text" id="pedido_id" name="pedido_id" placeholder="Ex: PED-1024">
                </div>

                <div class="campo">
                    <label for="cliente">Cliente</label>
                    <input type="text" id="cliente" name="cliente" placeholder="Nome do cliente" required>
                </div>

                <div class="campo campo-largo">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" placeholder="Rua, número, bairro, cidade" required>
                </div>

                <div class="campo">
                    <label for="data_prevista">Data prevista</label>
                    <input type="date" id="data_prevista" name="data_prevista">
                </div>

                <div class="campo">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option>Pendente</option>
                        <option>Em separação</option>
                        <option>Em transporte</option>
                        <option>Entregue</option>
                        <option>Cancelada</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="produto_id">Produto relacionado</label>
                    <select id="produto_id" name="produto_id">
                        <option value="">Selecione</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?= (int) $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="quantidade">Quantidade</label>
                    <input type="number" id="quantidade" name="quantidade" min="1" value="1">
                </div>

                <div class="campo campo-largo">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Ponto de referência, instruções, restrições..."></textarea>
                </div>

                <button type="submit" class="botao-principal">Salvar entrega</button>
            </form>
        </article>

        <aside class="painel painel-rota">
            <h2>Rota sugerida</h2>
            <ol id="rota-sugerida" class="rota-sugerida"></ol>
            <p class="rota-explicacao">
                A ordenação considera endereços e coordenadas, quando disponíveis. Sem GPS em tempo real,
                o sistema prioriza uma sequência lógica de atendimento para reduzir retrabalho e tempo de deslocamento.
            </p>
        </aside>
    </section>

    <section class="painel painel-lista">
        <div class="lista-header">
            <h2>Entregas cadastradas</h2>
            <span id="contador-entregas" class="contador">0</span>
        </div>

        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Destino</th>
                        <th>Prevista</th>
                        <th>Status</th>
                        <th>Qtd.</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody id="tbody-entregas"></tbody>
            </table>
        </div>
    </section>

    <section class="painel painel-lista">
        <div class="lista-header">
            <h2>Pedidos dos clientes</h2>
        </div>
        <div class="tabela-container">
            <table>
                <thead><tr><th>Pedido</th><th>Cliente</th><th>Produto</th><th>Entrega</th><th>Status</th></tr></thead>
                <tbody id="tbody-pedidos"></tbody>
            </table>
        </div>
    </section>
</main>

<script>
const produtosDisponiveis = <?= json_encode($produtos) ?>;

async function carregarEntregas() {
    const resposta = await fetch('../process/entregas.php?acao=listar', {
        headers: { 'Accept': 'application/json' }
    });
    const json = await resposta.json();
    const entregas = Array.isArray(json.dados) ? json.dados : [];

    const tbody = document.getElementById('tbody-entregas');
    const contador = document.getElementById('contador-entregas');
    const rota = document.getElementById('rota-sugerida');

    tbody.innerHTML = '';

    if (entregas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Nenhuma entrega cadastrada.</td></tr>';
        contador.textContent = '0';
        rota.innerHTML = '<li>Sem entregas pendentes no momento.</li>';
        return;
    }

    contador.textContent = String(entregas.length);

    entregas.forEach((entrega) => {
        const linha = document.createElement('tr');
        const produtos = Array.isArray(entrega.produtos_relacionados) ? entrega.produtos_relacionados : [];
        const nomeProduto = produtos.length > 0 ? produtos.map(item => item.nome || item.produto || 'Produto').join(', ') : 'Não informado';

        linha.innerHTML = `
            <td>${escHtml(entrega.pedido_id || '—')}</td>
            <td>${escHtml(entrega.cliente || '—')}</td>
            <td>${escHtml(entrega.endereco || '—')}</td>
            <td>${escHtml(entrega.data_prevista || '—')}</td>
            <td><span class="status status-${slug(entrega.status || 'Pendente')}">${escHtml(entrega.status || 'Pendente')}</span></td>
            <td>${escHtml(String(entrega.quantidade || 0))}</td>
            <td>
                <button type="button" class="botao-minimal" data-id="${entrega.id}" data-status="Entregue">Entregue</button>
                <button type="button" class="botao-minimal botao-alerta" data-id="${entrega.id}" data-status="Cancelada">Cancelar</button>
            </td>
        `;

        linha.title = nomeProduto;
        tbody.appendChild(linha);
    });

    document.querySelectorAll('[data-status]').forEach((botao) => {
        botao.addEventListener('click', async () => {
            const id = botao.dataset.id;
            const status = botao.dataset.status;
            await atualizarStatus(id, status);
        });
    });

    carregarRota();
}

async function carregarPedidos() {
    const resposta = await fetch('../process/pedidos.php');
    const json = await resposta.json();
    const tbody = document.getElementById('tbody-pedidos');
    if (!json.success || json.dados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Nenhum pedido recebido.</td></tr>';
        return;
    }
    tbody.innerHTML = json.dados.map((pedido) => `
        <tr>
            <td>${escHtml(pedido.codigo_pedido || '#' + pedido.id)}</td>
            <td>${escHtml(pedido.cliente_nome || 'Cliente')}</td>
            <td>${escHtml(pedido.produto)} (${pedido.quantidade})</td>
            <td>${escHtml(pedido.endereco_entrega || 'Não informado')}</td>
            <td><select data-pedido-status="${pedido.id}" data-pedido-codigo="${escHtml(pedido.codigo_pedido || '')}">
                ${['Pendente', 'Em preparação', 'Em transporte', 'Entregue', 'Cancelada'].map(status => `<option ${status === pedido.status ? 'selected' : ''}>${status}</option>`).join('')}
            </select></td>
        </tr>`).join('');
    tbody.querySelectorAll('[data-pedido-status]').forEach((select) => {
        select.addEventListener('change', () => atualizarPedido(select.dataset.pedidoStatus, select.dataset.pedidoCodigo, select.value));
    });
}

async function atualizarPedido(id, codigo, status) {
    const resposta = await fetch('../process/pedidos.php', {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, codigo_pedido: codigo, status})
    });
    const json = await resposta.json();
    if (!json.success) alert(json.message || 'Não foi possível atualizar o pedido.');
}

async function carregarRota() {
    const resposta = await fetch('../process/entregas.php?acao=rota', {
        headers: { 'Accept': 'application/json' }
    });
    const json = await resposta.json();
    const rota = document.getElementById('rota-sugerida');
    const dados = Array.isArray(json.dados) ? json.dados : [];

    rota.innerHTML = '';

    if (dados.length === 0) {
        rota.innerHTML = '<li>Sem entregas pendentes para otimizar.</li>';
        return;
    }

    dados.forEach((entrega, index) => {
        const item = document.createElement('li');
        item.innerHTML = `<span>${index + 1}</span><div><strong>${escHtml(entrega.cliente || 'Cliente')}</strong><small>${escHtml(entrega.endereco || 'Endereço')}</small></div>`;
        rota.appendChild(item);
    });
}

async function atualizarStatus(id, status) {
    const resposta = await fetch('../process/entregas.php', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id, status, observacoes: `Atualização automática para ${status}` })
    });

    const json = await resposta.json();
    if (json.success) {
        await carregarEntregas();
    } else {
        alert(json.message || 'Não foi possível atualizar a entrega.');
    }
}

async function salvarEntrega(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const produtoSelecionado = document.getElementById('produto_id');
    const nomeProduto = produtoSelecionado.options[produtoSelecionado.selectedIndex]?.text || 'Produto';
    const produtoId = produtoSelecionado.value ? Number(produtoSelecionado.value) : null;

    const payload = {
        pedido_id: form.pedido_id.value.trim() || null,
        cliente: form.cliente.value.trim(),
        endereco: form.endereco.value.trim(),
        data_prevista: form.data_prevista.value,
        status: form.status.value,
        quantidade: Number(form.quantidade.value || 1),
        observacoes: form.observacoes.value.trim(),
        produtos_relacionados: produtoId ? [{ produto_id: produtoId, nome: nomeProduto, quantidade: Number(form.quantidade.value || 1) }] : []
    };

    const resposta = await fetch('../process/entregas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    const json = await resposta.json();
    if (json.success) {
        form.reset();
        await carregarEntregas();
    } else {
        alert(json.message || 'Não foi possível salvar a entrega.');
    }
}

function escHtml(texto) {
    return String(texto)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function slug(value) {
    return String(value || 'pendente')
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '');
}

document.getElementById('form-entrega').addEventListener('submit', salvarEntrega);
carregarEntregas();
carregarPedidos();
</script>
</body>
</html>
