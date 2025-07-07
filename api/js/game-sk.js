function entrarpag() {
    window.location.href = "../login.html"; // Redireciona para a página de login
};




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




document.addEventListener("DOMContentLoaded", function () {
    var favoriteButton = document.getElementById("favorite-button");

    favoriteButton.addEventListener("click", function () {
        this.classList.toggle("red");
    });

    let images = [
        "../src/Capas/sekiro/capa.jpg",
        
        "../src/Capas/sekiro/gameplay1.jpg",

        "../src/Capas/sekiro/gameplay2.jpg",

        "../src/Capas/sekiro/gameplay3.jpg",

        "../src/Capas/sekiro/gameplay4.jpg"
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

    function changeImage(src, thumbElement) {
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
    }

    // Adicionar a função ao escopo global
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

