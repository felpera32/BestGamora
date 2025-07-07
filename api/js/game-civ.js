function entrarpag() {
    window.location.href = "../login.php"; // Redireciona para a página de login
}

document.addEventListener("DOMContentLoaded", function () {
    const favoriteButton = document.getElementById("favorite-button");
    
    if (favoriteButton) {
        favoriteButton.addEventListener("click", function () {
            // animação no clique
            this.classList.add("clicked");
            setTimeout(() => this.classList.remove("clicked"), 200);
            
            this.classList.toggle("red");

            const coracao = document.getElementById("coracao");
            if (coracao) {
                if (this.classList.contains("red")) {
                    //animação pulsando
                    favoriteButton.style.transform = "scale(1.1)";
                    setTimeout(() => favoriteButton.style.transform = "scale(1)", 150);
                    coracao.textContent = "FAVORITADO";
                } else {
                    coracao.textContent = "FAVORITAR";
                }
            }
        });
    }

    // Gerenciamento de imagens
    let images = [
        "../src/Capas/civ/capa.jpg",
        "../src/Capas/civ/gameplay1.jpg",
        "../src/Capas/civ/gameplay2.jpg",
        "../src/Capas/civ/gameplay3.jpg",
        "../src/Capas/civ/gameplay4.jpg"
    ];

    let currentIndex = 0;

    function updateImage() {
        document.getElementById('mainImage').src = images[currentIndex];

        // Atualizar thumbnails ativos
        document.querySelectorAll('.thumb').forEach((thumb, index) => {
            if (index === currentIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    document.getElementById('next').onclick = function () {
        currentIndex = (currentIndex + 1) % images.length;
        updateImage();
    };

    document.getElementById('prev').onclick = function () {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateImage();
    };

    // Função global para mudar imagem
    window.changeImage = function (src, thumbElement) {
        document.getElementById('mainImage').src = src;
        currentIndex = images.indexOf(src);
        if (currentIndex === -1) {
            currentIndex = 0;
        }

        // Atualizar thumbnails ativos
        document.querySelectorAll('.thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });

        thumbElement.classList.add('active');
    };
});

