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
        "https://meups.com.br/wp-content/uploads/2023/06/spider-scaled.jpg",
        
        "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2651280/ss_e4b67059ddedaeebd91fce113745f3eb99736f56.1920x1080.jpg?t=1750954033",

        "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2651280/ss_b4be948946130b7e140be82f24f1f9ccefae9117.1920x1080.jpg?t=1750954033",

        "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2651280/ss_f4140ba12158b812d9c1adc86c484d8e84b92e92.1920x1080.jpg?t=1750954033",

        "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2651280/ss_c6a0adf568d91d49a5c0f7f5e4df7e1cef71ee28.1920x1080.jpg?t=1750954033"
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

