<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Desejos - Gamora</title>
    <link rel="stylesheet" href="css/wishlist.css">
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="container">
        <div class="user-info">
            <span>Olá, Pedro</span>
        </div>

        <div class="header">
            <h1>Minha Lista de Desejos</h1>
        </div>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-number" id="total-games">0</span>
                <span class="stat-label">Jogos</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="total-value">R$ 0</span>
                <span class="stat-label">Valor Total</span>
            </div>
        </div>

        <div id="games-container">
            <div class="loading">Carregando lista de desejos...</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadWishlist();
        });

        function loadWishlist() {
            fetch('../get_wishlist.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('games-container');
                    
                    if (data.success && data.games.length > 0) {
                        displayGames(data.games);
                        updateStats(data.games);
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <h2>📋 Sua lista de desejos está vazia</h2>
                                <p>Adicione jogos que você gostaria de comprar no futuro.<br>
                                   Navegue pela nossa loja e clique no coração para favoritar!</p>
                                <a href="../index.php" class="btn-browse">🎮 Explorar Jogos</a>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('games-container').innerHTML = `
                        <div class="empty-state">
                            <h2>Erro ao carregar lista de desejos</h2>
                            <p>Tente novamente mais tarde.</p>
                        </div>
                    `;
                });
        }

        function displayGames(games) {
            const container = document.getElementById('games-container');
            const gamesGrid = document.createElement('div');
            gamesGrid.className = 'games-grid';

            games.forEach(game => {
                const gameCard = document.createElement('div');
                gameCard.className = 'game-card';
                gameCard.innerHTML = `
                    <img src="${game.imagemPrincipal || '../src/default-game.jpg'}" alt="${game.nome}" class="game-image">
                    <div class="game-info">
                        <h3 class="game-title">${game.nome}</h3>
                        <p class="game-developer">${game.fabricante || 'Desenvolvedor não informado'}</p>
                        <p class="game-price">R$ ${parseFloat(game.preco).toFixed(2)}</p>
                        <div class="game-actions">
                            <button class="btn btn-add-cart" onclick="addToCart(${game.idProduto}, '${game.nome}', ${game.preco})">
                                🛒 Adicionar ao Carrinho
                            </button>
                            <button class="btn btn-remove" onclick="removeFromWishlist(${game.idProduto})" title="Remover da lista">
                                🗑️
                            </button>
                        </div>
                    </div>
                `;
                gamesGrid.appendChild(gameCard);
            });

            container.innerHTML = '';
            container.appendChild(gamesGrid);
        }

        function updateStats(games) {
            const totalGames = games.length;
            const totalValue = games.reduce((sum, game) => sum + parseFloat(game.preco), 0);
            
            document.getElementById('total-games').textContent = totalGames;
            document.getElementById('total-value').textContent = `R$ ${totalValue.toFixed(2)}`;
        }

        function addToCart(productId, productName, productPrice) {
            const productDetails = {
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 1
            };

            fetch('../add_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(productDetails)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produto adicionado ao carrinho!');
                } else {
                    alert('Erro ao adicionar ao carrinho: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao adicionar ao carrinho');
            });
        }

        function removeFromWishlist(productId) {
            if (confirm('Tem certeza que deseja remover este jogo da sua lista de desejos?')) {
                fetch('../remove_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({id: productId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadWishlist(); // Recarregar a lista
                    } else {
                        alert('Erro ao remover: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao remover da lista');
                });
            }
        }
    </script>
</body>
</html>