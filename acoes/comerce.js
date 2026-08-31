let produtoAtual = null;
let categoriaAtual = null;

const chaveCarrinho = "carrinho_" + idUsuario;

let carrinho = JSON.parse(
    localStorage.getItem(chaveCarrinho)
) || [];

// =====================
// TROCAR TELAS
// =====================

function mostrarElemento(id) {
    document.querySelectorAll(".tela, .produto")
        .forEach(el => el.classList.remove("ativa"));

    const elemento = document.getElementById(id);

    if (elemento) {
        elemento.classList.add("ativa");
    }
    
    
    
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
      calcularGiro(p).toFixed(2),
      calcularConsumoMedio(p).toFixed(1),
      calcularCobertura(p).toFixed(0) + " dias",
      calcularRuptura(p).toFixed(1) + "%",
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




// =====================
// MOSTRAR PRODUTOS
// =====================

function mostrarVitrineGeral(filtro = '') {

    const container = document.getElementById("lista-produtos");

    if (!container) return;

    const termo = filtro.trim().toLowerCase();
    const produtosFiltrados = !termo
        ? produtos
        : produtos.filter((produto) => {
            const nome = (produto.nome || '').toLowerCase();
            const descricao = (produto.detalhe || '').toLowerCase();
            return nome.includes(termo) || descricao.includes(termo);
        });

    container.innerHTML = "";

    if (produtosFiltrados.length === 0) {
        container.innerHTML = `
            <div class="produtos produtos-vazio">
                <h2>Nenhum produto encontrado.</h2>
                <p>Experimente outro termo ou cadastre novos itens.</p>
            </div>
        `;
        return;
    }

    produtosFiltrados.forEach((produto, index) => {
        const produtoIndex = produtos.findIndex((item) => item.id === produto.id);

        const div = document.createElement("div");
        div.className = "produtos";
        div.onclick = () => abrirDetalhe(produtoIndex >= 0 ? produtoIndex : index);
        div.innerHTML = `
            <div class="produto-card-topo">
                <span class="badge-disponibilidade">${Number(produto.estoque_fisico || 0)} un.</span>
            </div>
            <h2>${produto.nome}</h2>
            <p class="preco">R$ ${Number(produto.preco).toFixed(2)}</p>
            <p class="info-produto">Disponível: ${produto.estoque_fisico ?? 0} un.</p>
        `;

        container.appendChild(div);
    });
}

function buscarCategorias() {
    const input = document.getElementById('busca-categorias');
    const termo = input ? input.value : '';
    mostrarVitrineGeral(termo);
}


// =====================
// DETALHE PRODUTO
// =====================

function abrirDetalhe(index) {

    produtoAtual = {
        ...produtos[index],
        quantidade: 1
    };

    atualizarTela();

    mostrarElemento("detalhe-produto");
}


function voltarParaCategoria() {

    mostrarElemento("categorias");

}


// =====================
// QUANTIDADE
// =====================

function calcular(acao) {

    if (!produtoAtual) return;

    if (acao === "soma") {

        if (produtoAtual.quantidade < produtoAtual.estoque_fisico) {
            produtoAtual.quantidade++;
        }

    }

    if (acao === "menos" && produtoAtual.quantidade > 1) {
        produtoAtual.quantidade--;
    }

    atualizarTela();
}

// =====================
// ATUALIZAR DETALHE
// =====================

function atualizarTela() {

    if (!produtoAtual) return;


    const preco = Number(produtoAtual.preco);


    document.getElementById("detalhe").innerText =
        produtoAtual.nome;


    document.getElementById("descricao").innerText =
        produtoAtual.detalhe || "Sem descrição";


    document.getElementById("quantidade").innerText =
        produtoAtual.quantidade;


    document.getElementById("preco").innerText =
        `Valor unitário: R$ ${preco.toFixed(2)}`;


    document.getElementById("total-produt").innerText =
        `Total: R$ ${(preco * produtoAtual.quantidade).toFixed(2)}`;

}



// =====================
// CARRINHO
// =====================

function salvarCarrinho(){

    localStorage.setItem(
        chaveCarrinho,
        JSON.stringify(carrinho)
    );

    atualizarCarrinho();
}



function adicionarCarrinho(){

    if(!produtoAtual) return;

    const existente = carrinho.find(
        item => item.id == produtoAtual.id
    );

    if(existente){
        existente.quantidade += produtoAtual.quantidade;
    } else {
        carrinho.push({
            ...produtoAtual,
            preco:Number(produtoAtual.preco)
        });
    }

    salvarCarrinho();
    atualizarCarrinho();
    mostrarElemento('carrinho');
    alert("Produto adicionado ao pedido!");

}

function removerItem(nome){

    carrinho = carrinho.filter(
        item => item.nome !== nome
    );

    salvarCarrinho();
atualizarEstoque();
}



function alterarQuantidadeCarrinho(nome, acao){

    const item = carrinho.find(
        produto => produto.nome === nome
    );


    if(!item) return;


    if(acao === "soma"){
        item.quantidade++;
    }


    if(acao === "menos"){

        item.quantidade--;

        if(item.quantidade <= 0){

            removerItem(nome);
            return;

        }

    }


    salvarCarrinho();

}



// =====================
// MOSTRAR CARRINHO
// =====================

function atualizarCarrinho(){

    const lista = document.querySelector(
        "#carrinho .carrinho-de-produtos"
    );


    if(!lista) return;


    lista.innerHTML = "";


    let total = 0;


    carrinho.forEach(item=>{


        const valor =
        Number(item.preco) * item.quantidade;


        total += valor;


        const li = document.createElement("li");


        li.innerHTML = `

        <div>

            <strong>${item.nome}</strong>

            <p>
            ${item.quantidade}x 
            R$ ${valor.toFixed(2)}
            </p>


            <button onclick="alterarQuantidadeCarrinho('${item.nome}','menos')">
            -
            </button>


            <button onclick="alterarQuantidadeCarrinho('${item.nome}','soma')">
            +
            </button>


            <button onclick="removerItem('${item.nome}')">
            ❌
            </button>


        </div>

        `;


        lista.appendChild(li);


    });


    document.getElementById("totalgeral").innerHTML =
    `Total: R$ ${total.toFixed(2)}`;

}







// =====================
// INICIALIZAÇÃO
// =====================
function atualizarEstoque(){

    fetch("../tela/Comercio.php", {
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify(carrinho)
    })
    .then(res => res.text())
    .then(dados => {
        console.log(dados);
    })
    .catch(erro => {
        console.error(erro);
    });
    
}


function atualizarCompra(){

    fetch("../process/atualizarvenda.php", {
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify(carrinho)
    })
    .then(res => res.text())
    .then(dados => {
        console.log(dados);
    })
    .catch(erro => {
        console.error(erro);
    });
    
}


window.addEventListener("load", ()=>{

    mostrarVitrineGeral();

    atualizarCarrinho();

});