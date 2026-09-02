//faz os calculos e a tabela
window.addEventListener("load", () => {
        gerarGraficoGiro();
        gerarGraficoConsumo();
        gerarGraficoCbertura();
        gerarGraficoRuptura();
    
});
function cor(nome){
    return getComputedStyle(document.documentElement)
        .getPropertyValue(nome)
        .trim();
}

const corTexto = cor("--cor-texto") || "#1F2937";
const corPrimaria = cor("--cor-primaria") || "#5B21B6";

function nivelRuptura(item, ruptura) {
    if (ruptura <= 10) return "Bom";
    if (ruptura <= 30) return "Medio";
    return "Ruim";
}

function nivelDeConsumoM(item, consumoM) {

    const estoqueTotal = Number(item.estoqueFisico) + Number(item.estoqueVirtual);

    if (consumoM === 0) return "Ruim";

    const cobertura = estoqueTotal / consumoM;

    if (cobertura <= 30) return "Bom";
    if (cobertura <= 60) return "Medio";
    return "Ruim";
}

function nivelGiroDeEstoque(giro) {
    if (giro >= 3) return "Bom";
    if (giro >= 1.6) return "Medio";
    return "Ruim";

}






//grafico de Giro de Estoque
function gerarGraficoGiro() {
    const canvas = document.getElementById("graficoGiro");
if (!canvas) return;

    const ctx = canvas.getContext("2d");
    ctx.clearRect(0,
        0,
        canvas.width,
        canvas.height);

    const valores = produtos.map(p => p.giro);
    const max = Math.max(...valores,
        1);
    const larguraBarra = 40;
    const gap = 20;
    const margem = 30;
    canvas.width = margem + produtos.length * (larguraBarra + gap) + 100;
    canvas.height = 300;

    valores.forEach((valor, i) => {
        const altura = (valor / max) * 200;
        let corTexto;

        if (valor >= 3) {
            ctx.strokeStyle = "#80ffa2";
            ctx.fillStyle = "rgba(128, 255, 162, 0.3)";
            corTexto = "#080";
        } else if (valor >= 1.6) {
            ctx.strokeStyle = "orange";
            ctx.fillStyle = "rgba(255, 165, 0, 0.3)";
            corTexto = "#e65c00";
        } else {
            ctx.strokeStyle = "red";
            ctx.fillStyle = "rgba(255, 0, 0, 0.3)";
            corTexto = "#c00";
        }
        const x = 50 + i * (larguraBarra + gap);
        ctx.lineWidth = 2;
        ctx.strokeRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        )
        ctx.fillRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        );
        const ytopo = 250 - altura;
        ctx.textAlign = "center";
        ctx.fillStyle = corTexto;
        ctx.fillText(valor.toFixed(0), x + larguraBarra/2, ytopo-5);

ctx.fillStyle = corPrimaria;
ctx.fillText(produtos[i].produto, x + larguraBarra/2, 270);
    });
}

//grafico de Consumo
function gerarGraficoConsumo() {

    const canvas =
    document.getElementById("graficoConsumo");
if (!canvas) return;

    const ctx = canvas.getContext("2d");

    ctx.clearRect(0,
        0,
        canvas.width,
        canvas.height);

const valores = produtos.map(p => {
    return p.consumo;
});

    const max = Math.max(...valores,
        1);

    const larguraBarra = 40;
    const gap = 20;

    const margem = 30;
    canvas.width = margem + produtos.length * (larguraBarra + gap) + 100;
    canvas.height = 300;
    valores.forEach((valor, i) => {
        const item = produtos[i];
        const altura = (valor / max) * 200;
        let corTexto;
const nivel = nivelDeConsumoM(item, valor);

if (nivel === "Bom") {
    ctx.strokeStyle = "#80ffa2";
    ctx.fillStyle = "rgba(128, 255, 162, 0.3)";
    corTexto = "#080";
}
else if (nivel === "Medio") {
    ctx.strokeStyle = "orange";
    ctx.fillStyle = "rgba(255, 165, 0, 0.3)";
    corTexto = "#e65c00";
}
else {
    ctx.strokeStyle = "red";
    ctx.fillStyle = "rgba(255, 0, 0, 0.3)";
    corTexto = "#c00";
}
        const x = 50 + i * (larguraBarra + gap);
        ctx.lineWidth = 2;
        ctx.strokeRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        )
        ctx.fillRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        );
        const ytopo = 250 - altura;
        ctx.textAlign = "center";
        ctx.fillStyle = corTexto;
        ctx.fillText(valor.toFixed(0), x + larguraBarra/2, ytopo-5);

ctx.fillStyle = corPrimaria;
ctx.fillText(produtos[i].produto, x + larguraBarra/2, 270);


    });
}

//grafico de Cobretura
function gerarGraficoCbertura() {
    const canvas =
    document.getElementById("graficoCobertura");
if (!canvas) return;

    const ctx = canvas.getContext("2d");

    ctx.clearRect(0,
        0,
        canvas.width,
        canvas.height);

const valores = produtos.map(p => p.cobertura
);


    const max = Math.max(...valores,
        1);

    const larguraBarra = 40;
    const gap = 20;

    const margem = 30;
    canvas.width = margem + produtos.length * (larguraBarra + gap) + 100;
    canvas.height = 300;
    valores.forEach((valor, i) => {
        const item = produtos[i];
        const altura = (valor / max) * 200;
        let corTexto;
const cobertura = item.cobertura;
        if (cobertura >= item.tempoRepo) {
            ctx.strokeStyle = "#80ffa2";
            ctx.fillStyle = "rgba(128, 255, 162, 0.3)";
            corTexto = "#080";
        } else if (cobertura >= item.tempoRepo *0.5) {
            ctx.strokeStyle = "orange";
            ctx.fillStyle = "rgba(255, 165, 0, 0.3)";
            corTexto = "#e65c00";
        } else {
            ctx.strokeStyle = "red";
            ctx.fillStyle = "rgba(255, 0, 0, 0.3)";
            corTexto = "#c00";
        }




        const x = 50 + i * (larguraBarra + gap);
        ctx.lineWidth = 2;
        ctx.strokeRect(x, 250 - altura, larguraBarra, altura);
        ctx.fillRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        );
        const ytopo = 250 - altura;
        ctx.textAlign = "center";
        ctx.fillStyle = corTexto;
        ctx.fillText(valor.toFixed(0), x + larguraBarra/2, ytopo-5);

ctx.fillStyle = corPrimaria;
ctx.fillText(produtos[i].produto, x+ larguraBarra/2, 270);


    });
}

//grafico de Ruptura
function gerarGraficoRuptura() {
    const canvas = document.getElementById("graficoRuptura");
if (!canvas) return;

    const ctx = canvas.getContext("2d");
    ctx.clearRect(0,
        0,
        canvas.width,
        canvas.height);

    const valores = produtos.map(p => {

const valorRuptura = p.ruptura;
return valorRuptura;

    });

    const max = 100;
    const larguraBarra = 40;
    const gap = 20;
    const margem = 30;
    canvas.width = margem + produtos.length * (larguraBarra + gap) + 100;
    canvas.height = 300;

    // 2. Desenha a escala lateral
    ctx.fillStyle = "#fff";
    ctx.textAlign = "right";
    for (let i = 0; i <= 5; i++) {
        // 0%, 20%, 40%, 60%, 80%, 100%
        const y = 250 - (i / 5) * 200;
        ctx.fillText((i * 20) + "%", 40, y + 5);
        // +5 pra alinhar verticalmente
        ctx.strokeStyle = "#555";
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(45, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
    }

    valores.forEach((valor, i) => {
        const altura = (valor / max) * 200;
        let corTexto;

        if (valor <= 10) {
            ctx.strokeStyle = "#80ffa2";
            ctx.fillStyle = "rgba(128, 255, 162, 0.3)";
            corTexto = "#080";
        } else if (valor <= 30) {
            ctx.strokeStyle = "orange";
            ctx.fillStyle = "rgba(255, 165, 0, 0.3)";
            corTexto = "#e65c00";
        } else {
            ctx.strokeStyle = "red";
            ctx.fillStyle = "rgba(255, 0, 0, 0.3)";
            corTexto = "#c00";
        }
        const x = 50 + i * (larguraBarra + gap);
        ctx.lineWidth = 2;
        ctx.strokeRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        )
        ctx.fillRect(
            x,
            250 - altura,
            larguraBarra,
            altura
        );
        const ytopo = 250 - altura;
        ctx.textAlign = "center";
        ctx.fillStyle = corTexto;
        ctx.fillText(valor.toFixed(0)+ "%", x + larguraBarra/2, ytopo-5);

ctx.fillStyle = corPrimaria;
ctx.fillText(produtos[i].produto, x + larguraBarra/2, 270);
    });
}
