
window.addEventListener("load", () => {
    mostrarRelatorio();
});

// =====================
// CORES DOS NÍVEIS
// =====================
function corNivel(nivel) {
    const cores = {
        Bom: "#70c474",
        Medio: "#c68c3f",
        Ruim: "#FF6347"
    };
    return cores[nivel] || "#fff";
}
// =====================
// CLASSIFICAÇÕES
// =====================
function nivelGiro(giro) {
    if (giro >= 3) return "Bom";
    if (giro >= 1.6) return "Medio";
    return "Ruim";
}

function nivelConsumo(consumo) {
    if (consumo <= 5) return "Bom";
    if (consumo <= 15) return "Medio";
    return "Ruim";
}

function nivelCobertura(item, cobertura) {
    if (cobertura >= item.tempoRepo) return "Bom";
    if (cobertura >= item.tempoRepo * 0.5) return "Medio";
    return "Ruim";
}

function nivelRuptura(ruptura) {
    if (ruptura <= 10) return "Bom";
    if (ruptura <= 30) return "Medio";
    return "Ruim";
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
      (p.giro || 0).toFixed(2),
      (p.consumo || 0).toFixed(1),
      (p.cobertura || 0).toFixed(0) + " dias",
      (p.ruptura || 0).toFixed(1) + "%",
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
      const nivel = nivelGiro(produto.giro);
      data.cell.styles.fillColor = corNivel(nivel);
    }
        if (data.column.index === 6) {
      const nivel = nivelConsumo(produto.consumo);
      data.cell.styles.fillColor = corNivel(nivel);
    }

    // Cobertura
    if (data.column.index === 7) {
      const nivel = nivelCobertura(produto, produto.cobertura);
      data.cell.styles.fillColor = corNivel(nivel);
    }

    // Ruptura
    if (data.column.index === 8) {
      const nivel = nivelRuptura(produto.ruptura);
      data.cell.styles.fillColor = corNivel(nivel);

    }

  }

}  


  });
  doc.save("relatorio de indicadores.pdf");
}

// =====================
// RELATÓRIO
// =====================
function mostrarRelatorio() {
    const container = document.getElementById("relatorio");
    container.innerHTML = "";

    const dados = {
        giro: { Bom: [], Medio: [], Ruim: [] },
        consumo: { Bom: [], Medio: [], Ruim: [] },
        cobertura: { Bom: [], Medio: [], Ruim: [] },
        ruptura: { Bom: [], Medio: [], Ruim: [] }
    };

    produtos.forEach(p => {

        const giro = Number(p.giro) || 0;
        const consumo = Number(p.consumo) || 0;
        const cobertura = Number(p.cobertura) || 0;
        const ruptura = Number(p.ruptura) || 0;

        dados.giro[nivelGiro(giro)].push({
            nome: p.produto,
            valor: giro
        });

        dados.consumo[nivelConsumo(consumo)].push({
            nome: p.produto,
            valor: consumo
        });

        dados.cobertura[nivelCobertura(p, cobertura)].push({
            nome: p.produto,
            valor: cobertura
        });

        dados.ruptura[nivelRuptura(ruptura)].push({
            nome: p.produto,
            valor: ruptura
        });
    });

    container.innerHTML = `

        ${renderBloco("Giro de Estoque", dados.giro)}
        ${renderBloco("Consumo Médio", dados.consumo)}
        ${renderBloco("Cobertura de Estoque", dados.cobertura)}
        ${renderBloco("Ruptura", dados.ruptura, "%")}
    `;
}// =====================
// TEMPLATE REUTILIZÁVEL
// =====================
function renderBloco(titulo, dadosNivel, sufixo = "") {

    return `
        <div class="container-relatorioN">
            <h2>${titulo}</h2>

            <div class="container-coluna">

                ${["Bom", "Medio", "Ruim"].map(nivel => `
                    <div class="coluna" style="box-shadow: 0 1px 4px ${corNivel(nivel)}">
                        <h3>${nivel}</h3>

                        ${dadosNivel[nivel].length
                            ? dadosNivel[nivel].map(p => `
                                <p>
                                    ${p.nome} - ${Number(p.valor).toFixed(1)}${sufixo}
                                </p>
                            `).join("")
                            : "<p>Sem produtos</p>"
                        }

                    </div>
                `).join("")}

            </div>
        </div>
    `;
}