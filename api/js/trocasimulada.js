// Variáveis globais
let selectedMyGames = [];
let selectedOtherGames = [];
let currentOtherUserId = null;

// Função para selecionar jogos
function selectGame(element, type, gameId, price) {
    const isSelected = element.classList.contains('selected');
    
    if (isSelected) {
        // Desselecionar
        element.classList.remove('selected');
        if (type === 'meu') {
            selectedMyGames = selectedMyGames.filter(game => game.id !== gameId);
        } else {
            selectedOtherGames = selectedOtherGames.filter(game => game.id !== gameId);
        }
    } else {
        // Selecionar
        element.classList.add('selected');
        if (type === 'meu') {
            selectedMyGames.push({
                id: gameId,
                price: price,
                element: element
            });
        } else {
            selectedOtherGames.push({
                id: gameId,
                price: price,
                element: element
            });
        }
    }
    
    updateValues();
    validateTrade();
}

// Função para atualizar valores
function updateValues() {
    const myTotal = selectedMyGames.reduce((sum, game) => sum + game.price, 0);
    const otherTotal = selectedOtherGames.reduce((sum, game) => sum + game.price, 0);
    
    document.getElementById('valor-meus-jogos').textContent = 
        'R$ ' + myTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    document.getElementById('valor-outros-jogos').textContent = 
        'R$ ' + otherTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Função para validar troca
function validateTrade() {
    const tradeStatus = document.getElementById('trade-status');
    const confirmBtn = document.getElementById('confirm-btn');
    
    if (selectedMyGames.length === 0 || selectedOtherGames.length === 0) {
        tradeStatus.style.display = 'none';
        confirmBtn.disabled = true;
        return;
    }
    
    let isValidTrade = true;
    let statusMessage = '';
    
    // Verificar se cada jogo meu pode ser trocado por algum jogo do outro
    for (let myGame of selectedMyGames) {
        const myElement = myGame.element;
        const myMin = parseFloat(myElement.dataset.min);
        const myMax = parseFloat(myElement.dataset.max);
        
        let canTradeMyGame = false;
        for (let otherGame of selectedOtherGames) {
            if (otherGame.price >= myMin && otherGame.price <= myMax) {
                canTradeMyGame = true;
                break;
            }
        }
        
        if (!canTradeMyGame) {
            isValidTrade = false;
            statusMessage = 'Alguns jogos estão fora da faixa de preço permitida';
            break;
        }
    }
    
    // Verificar se cada jogo do outro pode ser trocado por algum meu
    if (isValidTrade) {
        for (let otherGame of selectedOtherGames) {
            const otherElement = otherGame.element;
            const otherMin = parseFloat(otherElement.dataset.min);
            const otherMax = parseFloat(otherElement.dataset.max);
            
            let canTradeOtherGame = false;
            for (let myGame of selectedMyGames) {
                if (myGame.price >= otherMin && myGame.price <= otherMax) {
                    canTradeOtherGame = true;
                    break;
                }
            }
            
            if (!canTradeOtherGame) {
                isValidTrade = false;
                statusMessage = 'Alguns jogos estão fora da faixa de preço permitida';
                break;
            }
        }
    }
    
    if (isValidTrade) {
        statusMessage = '✅ Troca válida!';
        tradeStatus.className = 'trade-status valid';
        confirmBtn.disabled = false;
    } else {
        tradeStatus.className = 'trade-status invalid';
        confirmBtn.disabled = true;
    }
    
    tradeStatus.textContent = statusMessage;
    tradeStatus.style.display = 'block';
}

// Função para mudar usuário
async function changeUser(userId) {
    const jogosOutros = document.getElementById('jogos-outros');
    const otherUserHeader = document.getElementById('other-user-header');
    const otherUserName = document.getElementById('other-user-name');
    
    // Limpar seleções anteriores
    selectedOtherGames = [];
    updateValues();
    validateTrade();
    
    if (!userId) {
        jogosOutros.innerHTML = '<div class="no-games">Selecione um usuário para ver seus jogos</div>';
        otherUserHeader.style.display = 'none';
        currentOtherUserId = null;
        return;
    }
    
    currentOtherUserId = userId;
    jogosOutros.innerHTML = '<div class="loading">Carregando jogos...</div>';
    
    try {
        const response = await fetch('get_user_games.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ userId: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            otherUserName.textContent = data.userName;
            otherUserHeader.style.display = 'flex';
            
            if (data.games.length === 0) {
                jogosOutros.innerHTML = '<div class="no-games">Este usuário não possui jogos</div>';
            } else {
                let gamesHtml = '';
                data.games.forEach(jogo => {
                    const preco = parseFloat(jogo.preco);
                    const min = (preco * 0.8).toFixed(2);
                    const max = (preco * 1.2).toFixed(2);
                    const precoFormatado = preco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const minFormatado = (preco * 0.8).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const maxFormatado = (preco * 1.2).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                    // Função para lidar com erro de imagem
                    const imageError = "this.style.display='none'; this.parentElement.innerHTML='<div class=\\'no-image\\'>Sem Imagem</div>'";
                    
                    gamesHtml += `
                        <div class="game-item" onclick="selectGame(this, 'outro', ${jogo.idProduto}, ${preco})" 
                             data-price="${preco}" data-min="${min}" data-max="${max}">
                            <div class="game-image">
                                ${jogo.imagemPrincipal ? 
                                    `<img src="${jogo.imagemPrincipal}" alt="${jogo.nome}" onerror="${imageError}">` : 
                                    '<div class="no-image">Sem Imagem</div>'
                                }
                            </div>
                            <div class="game-info">
                                <h3>${jogo.nome}</h3>
                                <div class="game-price">R$ ${precoFormatado}</div>
                                <div class="price-range">Aceita: R$ ${minFormatado} - R$ ${maxFormatado}</div>
                            </div>
                        </div>
                    `;
                });
                jogosOutros.innerHTML = gamesHtml;
            }
        } else {
            jogosOutros.innerHTML = `<div class="no-games">Erro: ${data.error}</div>`;
            otherUserHeader.style.display = 'none';
        }
    } catch (error) {
        console.error('Erro ao buscar jogos:', error);
        jogosOutros.innerHTML = '<div class="no-games">Erro ao carregar jogos</div>';
        otherUserHeader.style.display = 'none';
    }
}

// Função para confirmar troca
function confirmarTroca() {
    if (selectedMyGames.length === 0 || selectedOtherGames.length === 0) {
        alert('Selecione jogos de ambos os usuários para realizar a troca');
        return;
    }
    
    const myGamesNames = selectedMyGames.map(game => 
        game.element.querySelector('.game-info h3').textContent
    );
    const otherGamesNames = selectedOtherGames.map(game => 
        game.element.querySelector('.game-info h3').textContent
    );
    
    const myTotal = selectedMyGames.reduce((sum, game) => sum + game.price, 0);
    const otherTotal = selectedOtherGames.reduce((sum, game) => sum + game.price, 0);
    
    const confirmMessage = `
Confirmar troca:

SEUS JOGOS:
${myGamesNames.join('\n')}
Total: R$ ${myTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}

POR:

JOGOS DO USUÁRIO:
${otherGamesNames.join('\n')}
Total: R$ ${otherTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}

Deseja confirmar esta troca?
    `;
    
    if (confirm(confirmMessage)) {
        alert('Troca confirmada! (Esta é uma simulação)');
        
        // Resetar seleções
        selectedMyGames.forEach(game => game.element.classList.remove('selected'));
        selectedOtherGames.forEach(game => game.element.classList.remove('selected'));
        selectedMyGames = [];
        selectedOtherGames = [];
        
        updateValues();
        validateTrade();
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página carregada');
    updateValues();
});