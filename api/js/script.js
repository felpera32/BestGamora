function entrarpag() {
    window.location.href = "login.html"; // Redireciona para a página de login
}

function toggleFavorite(button) {
    // Alterna a classe "favorito" no botão
    button.classList.toggle('favorito');
}



function toggleFavorite(button) {
    let heart = button.querySelector(".heart, .heart2"); // Captura os dois tipos
    if (heart.classList.contains("favorito")) {
        heart.classList.remove("favorito");
        heart.innerHTML = "&#9825;"; // Coração branco
    } else {
        heart.classList.add("favorito");
        heart.innerHTML = "&#9829;"; // Coração vermelho
    }
}
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("coracao").addEventListener("click", function () {
        this.classList.toggle("red");
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const coracoes = document.querySelectorAll(".coracao2");
    
    coracoes.forEach(function(coracao) {
        coracao.addEventListener("click", function () {
            this.classList.toggle("red");
        });
    });
});
