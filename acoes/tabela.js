window.addEventListener("load", () => {
    mostrarTabela();
});
function mostrarTabela() {
    const grafico = document.getElementById('graficos');
    grafico.innerHTML = "";

    if (produtos.length === 0) {
grafico.innerHTML = `
<tr>
    <td colspan="7">Não há produtos</td>
</tr>`;
return;
    }

    const termo = (document.getElementById('pesquisa-produto')?.value || '').toLowerCase();
    const periodo = document.getElementById('filtro-periodo')?.value || 'todos';
    const produtosVisiveis = produtos.filter(item => {
      const nomeCorresponde = String(item.produto).toLowerCase().includes(termo);
      const periodoCorresponde = periodo === 'todos' || item.periodo === periodo;
      return nomeCorresponde && periodoCorresponde;
    });

    if (produtosVisiveis.length === 0) {
      grafico.innerHTML = '<tr><td colspan="7">Nenhum produto encontrado.</td></tr>';
      return;
    }

    produtosVisiveis.forEach((item) => {
      const index = produtos.indexOf(item);
      const estoqueTotal = Number(item.estoqueFisico) + Number(item.estoqueVirtual);
      const classeEstoque = estoqueTotal === 0 ? 'estoque-zerado' : (estoqueTotal <= Number(item.tempoRepo) ? 'estoque-baixo' : '');

        const linha = document.createElement("tr");
      linha.className = classeEstoque;
      linha.title = estoqueTotal === 0 ? 'Estoque zerado' : (classeEstoque ? 'Estoque baixo: reposição necessária' : 'Estoque normal');

        linha.innerHTML = `
            <td>${item.produto}</td>
            <td>${item.giro.toFixed(1)}</td>
            <td>${item.consumo.toFixed(2)}</td>
            <td>${item.cobertura.toFixed(0)}</td>
            <td>${item.ruptura.toFixed(1)}%</td>
  
            <td><button onclick="editar(${index})">Editar</button></td>
    <td class= "excluir" onclick="excluirItem(${index})">Excluir</td>
        `;

        grafico.appendChild(linha);
    });

}

function filtrarTabela() {
  mostrarTabela();
}
//fechar o formulario de editar se mudar o valor
function voltar() {
    document.getElementById('editarFor').style.display = "none";

}

let produtoEmEdicao = null;

function editar(index) {
  produtoEmEdicao = produtos[index];
  document.getElementById('noproduto').value = produtoEmEdicao.produto;
  document.getElementById('esFisico').value = produtoEmEdicao.estoqueFisico;
  document.getElementById('esvirtual').value = produtoEmEdicao.estoqueVirtual;
  document.getElementById('TotalDevendas').value = produtoEmEdicao.vendaTotal;
  document.getElementById('tempo').value = produtoEmEdicao.tempoRepo;
  document.getElementById('totalPedido').value = produtoEmEdicao.totalPedidos;
  document.getElementById('editarFor').style.display = "block";
}

async function salvarEdicao() {
  if (!produtoEmEdicao) return;
  const resposta = await fetch('../process/editarProduto.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: produtoEmEdicao.id,
      nome: document.getElementById('noproduto').value,
      estoque_fisico: document.getElementById('esFisico').value,
      estoque_virtual: document.getElementById('esvirtual').value,
      total_vendas: document.getElementById('TotalDevendas').value,
      tempo_reposicao: document.getElementById('tempo').value,
      total_pedidos: document.getElementById('totalPedido').value
    })
  });
  const resultado = await resposta.json();
  if (!resultado.success) {
    alert(resultado.message || 'Não foi possível editar o produto.');
    return;
  }
  Object.assign(produtoEmEdicao, {
    produto: document.getElementById('noproduto').value,
    estoqueFisico: Number(document.getElementById('esFisico').value),
    estoqueVirtual: Number(document.getElementById('esvirtual').value),
    vendaTotal: Number(document.getElementById('TotalDevendas').value),
    tempoRepo: Number(document.getElementById('tempo').value),
    totalPedidos: Number(document.getElementById('totalPedido').value)
  });
  voltar();
  mostrarTabela();
}


function excluirItem(index) {
    console.log("Entrou na função excluir");
const produto = produtos[index];

    fetch("../process/deletarProduto.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id: produto.id
        })
    })
    .then(res => res.text())
    .then(dados => {
        console.log(dados);
produtos.splice(index, 1);
mostrarTabela();    }
)
    .catch(erro => {
        console.error(erro);
    });
    
    mostrarTabela();

}


// =====================
// GERAR PDF
// =====================
function gerarPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  if (produtos.length === 0) {
        alert("Cadastre pelo menos um produto antes de gerar o relatório.");
        return;
    }
  doc.text("Relatório de Indicadores de desempenho", 10, 10);

  // Monta os dados da tabela
  const dados = produtos.map(p => {
    return [
      p.produto,
      p.estoqueFisico,
      p.estoqueVirtual,
      p.vendaTotal, p.totalPedidos,
      p.giro.toFixed(2),
      p.consumo.toFixed(1),
      p.cobertura.toFixed(0) + " dias",
      p.ruptura.toFixed(1) + "%",
      p.periodo
    ];
  });

  // Define os cabeçalhos
  const cabecalho = ["Produto", "Estoque fisico", "Estoque virtual", "Total de vendas", "Total de pedidos", "Giro", "Consumo médio", "Cobertura", "Ruptura", "Periodo"];

  // Adiciona a tabela
  doc.autoTable({
    head: [cabecalho],
    body: dados,
    startY: 20, // Começa abaixo do título
    theme: 'grid',
       headStyles: { fillColor: [20, 20, 20], textColor: 255 },

       columnStyles: {

    0: { cellWidth: 19 }, // Produto
    1: { cellWidth: 20 }, // Estoque físico
    2: { cellWidth: 20 }, // Estoque virtual
    3: { cellWidth: 20 }, // Total de vendas
    4: { cellWidth: 20 }, // Total de pedidos
    5: { cellWidth: 15 }, // Giro
    6: { cellWidth: 20 }, // Consumo médio
    7: { cellWidth: 20 }, // Cobertura
    8: { cellWidth: 20 }, // Ruptura
    9: { cellWidth: 17}  //
       },
didParseCell: function (data) {


  if (data.section === "body") {

    const produto = produtos[data.row.index];

    // Giro
    if (data.column.index === 5) {
      const giro = calcularGiro(produto);
      const nivel = nivelGiroDeEstoque(giro);
      data.cell.styles.fillColor = corNivel(nivel);
    }
        if (data.column.index === 6) {
const consumo = calcularConsumoMedio(produto);
      const nivel = nivelDeConsumoM(produto, consumo);
      data.cell.styles.fillColor = corNivel(nivel);
    }

    // Cobertura
    if (data.column.index === 7) {
      const cobertura = calcularCobertura(produto);
      const nivel = nivelCobertura(produto, cobertura);
      data.cell.styles.fillColor = corNivel(nivel);
    }

    // Ruptura
    if (data.column.index === 8) {
      const ruptura = calcularRuptura(produto);
      const nivel = nivelRuptura(produto, ruptura);
      data.cell.styles.fillColor = corNivel(nivel);
    }

  }

}  


  });
  doc.save("relatorio de indicadores.pdf");
}




function mostrarIndicador(indicador) {
    // Pega todas as linhas, inclusive o header
const linhas = document.querySelectorAll("#header-graficos tr, #graficos tr");




    linhas.forEach(linha => {
        
      const colunas = linha.children;
        // Pega os filhos da linha (colunas)

        for (let i = 0; i < colunas.length; i++) {
            if (indicador === "todos" || i === 0 || i === indicador) {
                colunas[i].style.display = "";
            } else {
                colunas[i].style.display = "none";
            }
        }
    });
    
  document.querySelectorAll('.botao-filtro').forEach(b => b.classList.remove('ativo'));
const botaoAtivo = Array.from(document.querySelectorAll('.botao-filtro'))

.find(b => b.getAttribute('onclick').includes(`mostrarIndicador('${indicador}')`));
if (botaoAtivo) botaoAtivo.classList.add('ativo');

}



//botao de Excluir calculo
const abrirformapagartudo =
document.getElementById('apagarTudo');
const formapagar =
document.getElementById('form-excluir');
const apagarTudo =
document.querySelector('.confirmar');
const cancelar =
document.querySelector('.cancelar');

abrirformapagartudo.addEventListener("click", () => {
    formapagar.style.display = "block";
});
apagarTudo.addEventListener("click", () => {
  fetch("../process/deletarProduto.php", {
     method: "POST"
      
  })
  
  
        
          .then(res => res.text())
    .then(dados => {
        console.log(dados);
    produtos.length = 0;
    mostrarTabela();
    formapagar.style.display = "none";
})
  .catch(erro => {
        console.error(erro);
    });

});
cancelar.addEventListener("click", () => {
    formapagar.style.display = "none";
});
