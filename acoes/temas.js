const configuracao = {
    tema: "roxo",
    modo: "claro"
};

const temas = {
    roxo: {
        claro: {
            fundo: "#F4F3FF",
            primaria: "#5B21B6",
            texto: "#1F2937",
            borda: "#DDD6FE"
        },
        escuro: {
            fundo: "#0F1025",
            primaria: "#8B5CF6",
            texto: "#E5E7EB",
            borda: "#312E81"
        }
    },

    // azul, verde, vermelho...
};

function aplicarTema(tema, modo) {
    const t = temas[tema]?.[modo];

    if (!t) return;

    document.documentElement.style.setProperty("--cor-fundo", t.fundo);
    document.documentElement.style.setProperty("--cor-primaria", t.primaria);
    document.documentElement.style.setProperty("--cor-texto", t.texto);
    document.documentElement.style.setProperty("--cor-borda", t.borda);

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute("content", t.primaria);
    }
}

function atualizarTema() {
    aplicarTema(configuracao.tema, configuracao.modo);
}

function alternarModo() {
    configuracao.modo =
        configuracao.modo === "claro" ? "escuro" : "claro";

    atualizarTema();
}

const botaoTema = document.getElementById("theme-toggle");
if (botaoTema) {
    botaoTema.addEventListener("click", alternarModo);
}

function trocarTema(nome) {
    if (temas[nome]) {
        configuracao.tema = nome;
        atualizarTema();
    }
}

atualizarTema();