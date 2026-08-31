




//amarzena os valores dos inputs
function armazenarValor() {

    const form = document.getElementById("informacoes");

    const dados = new FormData(form);

fetch("../process/salvar_produto.php", {
    method: "POST",
    body: dados
})
.then(resposta => resposta.text())
.then(resultado => {

    const mensagem = document.createElement("div");
    mensagem.className = "mensagem";
    mensagem.textContent = resultado;

    document.body.appendChild(mensagem);

    setTimeout(() => {
        mensagem.remove();
    }, 3000);

    form.reset();
    carregarDados();
}).catch(erro => {
    console.error(erro);
    alert("Erro ao salvar.");
});
}




async function carregarDados() {
    const res = await fetch("../process/indicadores.php");
    produtos = await res.json();
    mostrarTabela();
}

window.addEventListener("load", carregarDados);
