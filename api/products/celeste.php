<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo: Celeste</title>
    <link rel="stylesheet" href="../css/games.css">
    <script src="../js/game-celeste.js"></script>
</head>

<body>
    <header>

    <?php 


        session_start();

        if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ../login.php');
            exit;
        }

        if (!isset($_SESSION['biblioteca'])) {
            $_SESSION['biblioteca'] = [];
        }

        $bibliotecaVazia = empty($_SESSION['biblioteca']);

        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'gamoraloja');

         include "../navbar/GameNav.php";
    ?>
    </header>

    <div class="main-container">
        <div class="left-column">
            <div class="game-image-container">
                <button class="nav prev" id="prev">&#10094;</button>
                <img id="mainImage" src="../src/Capas/celeste/capa.jpg" alt="Celeste">
                <button class="nav next" id="next">&#10095;</button>
            </div>
            <div class="thumbnails">
                <img class="thumb active" src="../src/Capas/celeste/Capa.jpg" alt="Thumbnail 1"
                    onclick="changeImage('../src/Capas/celeste/capa.jpg', this)">

                <img class="thumb" src="../src/Capas/celeste/gameplay1.jpg" alt="Thumbnail 1"
                    onclick="changeImage('../src/Capas/celeste/gameplay1.jpg', this)">

                <img class="thumb" src="../src/Capas/celeste/gameplay2.jpg" alt="Thumbnail 2"
                    onclick="changeImage('../src/Capas/celeste/gameplay2.jpg', this)">

                <img class="thumb" src="../src/Capas/celeste/gameplay3.jpg" alt="Thumbnail 3"
                    onclick="changeImage('../src/Capas/celeste/gameplay3.jpg', this)">

                <img class="thumb" src="../src/Capas/celeste/gameplay4.jpg" alt="Thumbnail 4"
                    onclick="changeImage('../src/Capas/celeste/gameplay4.jpg', this)">
            </div>
        </div>

        <div class="right-column">
            <div class="game-info">
                <h1>Celeste</h1>
                <p>Desenvolvedor: <strong>Maddy Makes Games</strong></p>
                <p class="price">R$ 40,00</p>
            </div>
            <div class="button-container">
                <button class="favorite" id="favorite-button">
                    <span class="coracao" id="coracao">FAVORITAR</span>
                </button>

                <button class="add-to-cart">Adicionar ao Carrinho</button>

            </div>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addToCartButton = document.querySelector('.add-to-cart');

            if (addToCartButton) {
                addToCartButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    addToCartButton.disabled = true;
                    addToCartButton.classList.add('clicked');
                    addToCartButton.style.transform = 'scale(0.95)';

                    const productDetails = {
                        id: 18,
                        name: 'Celeste',
                        price: 40.00,
                        quantity: 1
                    };

                    console.log('Enviando dados:', productDetails);

                    fetch('../add_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(productDetails)
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Resposta do servidor:', data);

                            if (data.success) {
                                addToCartButton.classList.add('success');
                                addToCartButton.textContent = 'Adicionado!';
                                setTimeout(() => {
                                    addToCartButton.classList.remove('success');
                                    addToCartButton.textContent = 'Adicionar ao Carrinho';
                                }, 2000);
                            } else {
                                throw new Error(data.message || 'Erro desconhecido');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            addToCartButton.classList.add('error');
                            alert('Erro: ' + error.message);
                            setTimeout(() => {
                                addToCartButton.classList.remove('error');
                            }, 2000);
                        })
                        .finally(() => {
                            addToCartButton.classList.remove('clicked');
                            addToCartButton.style.transform = 'scale(1)';
                            addToCartButton.disabled = false;
                        });
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                const favoriteButton = document.getElementById('favorite-button');
                const coracao = document.getElementById('coracao');
                
                if (favoriteButton) {
                    favoriteButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        favoriteButton.disabled = true;
                        favoriteButton.classList.add('clicked');
                        
                        // Dados do jogo - certifique-se de que o ID está correto
                        const gameDetails = {
                            id: 18, // Certifique-se de que este ID existe na tabela produtos
                            name: 'Celeste',
                            price: 40.00,
                            developer: 'Maddy Makes Games',
                            image: '../src/Capas/celeste/capa.jpg'
                        };
                        
                        console.log('Adicionando aos favoritos:', gameDetails);
                        
                        fetch('../add_wishlist.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(gameDetails)
                        })
                        .then(response => {
                            console.log('Status da resposta:', response.status);
                            
                            if (!response.ok) {
                                return response.text().then(text => {
                                    console.error('Resposta de erro:', text);
                                    throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Resposta do servidor:', data);
                            
                            if (data.success) {
                                favoriteButton.classList.add('favorited');
                                coracao.textContent = 'FAVORITADO!';
                                coracao.style.color = '#ff4757';
                                
                                // Mostrar mensagem de sucesso
                                const successMessage = document.createElement('div');
                                successMessage.style.cssText = `
                                    position: fixed;
                                    top: 20px;
                                    right: 20px;
                                    background: #4CAF50;
                                    color: white;
                                    padding: 15px;
                                    border-radius: 5px;
                                    z-index: 1000;
                                    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                `;
                                successMessage.textContent = 'Jogo adicionado aos favoritos!';
                                document.body.appendChild(successMessage);
                                
                                // Remover mensagem após 3 segundos
                                setTimeout(() => {
                                    document.body.removeChild(successMessage);
                                }, 3000);
                                
                                // Opcional: perguntar se quer ir para lista de desejos
                                setTimeout(() => {
                                    if (confirm('Deseja ver sua lista de desejos?')) {
                                        window.location.href = '../pages/wishlist.php';
                                    }
                                }, 1500);
                            } else {
                                throw new Error(data.message || 'Erro ao adicionar aos favoritos');
                            }
                        })
                        .catch(error => {
                            console.error('Erro completo:', error);
                            favoriteButton.classList.add('error');
                            
                            // Mostrar mensagem de erro mais detalhada
                            let errorMessage = 'Erro ao adicionar aos favoritos';
                            if (error.message.includes('não logado')) {
                                errorMessage = 'Você precisa estar logado para favoritar jogos';
                            } else if (error.message.includes('já está na lista')) {
                                errorMessage = 'Este jogo já está na sua lista de desejos';
                            } else {
                                errorMessage = error.message;
                            }
                            
                            alert(errorMessage);
                            
                            setTimeout(() => {
                                favoriteButton.classList.remove('error');
                            }, 2000);
                        })
                        .finally(() => {
                            favoriteButton.classList.remove('clicked');
                            favoriteButton.disabled = false;
                        });
                    });
                }
            });
                }
            });
        });
    </script>



    <style>
        .add-to-cart {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .add-to-cart:hover {
            background-color: #218838;
        }

        .add-to-cart.clicked {
            transform: scale(0.95);
            opacity: 0.9;
        }

        .add-to-cart.success {
            background-color: #218838;
            color: white;
        }

        .add-to-cart.error {
            background-color: #dc3545;
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .perfil-foto {
            width: 46px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</body>

</html>