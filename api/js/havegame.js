// Adicionar esta função ao JavaScript da página de produtos (index.php ou similar)

function verificarEAdicionarAoCarrinho(idProduto, nomeProduto) {
    fetch('verificar_biblioteca.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idProduto=${idProduto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.jaTemJogo) {
            // Mostrar mensagem de que já possui o jogo
            alert(`Você já possui "${nomeProduto}" na sua biblioteca!`);
            
            // Opcional: Redirecionar para a biblioteca
            const irParaBiblioteca = confirm('Deseja ir para sua biblioteca?');
            if (irParaBiblioteca) {
                window.location.href = 'biblioteca.php';
            }
        } else {
            // Prosseguir com a adição ao carrinho
            adicionarAoCarrinho(idProduto);
        }
    })
    .catch(error => {
        console.error('Erro ao verificar biblioteca:', error);
        adicionarAoCarrinho(idProduto);
    });
}

function adicionarAoCarrinho(idProduto) {
    // Sua função existente para adicionar ao carrinho
    fetch('adicionar_ao_carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${idProduto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Produto adicionado ao carrinho!');
            // Atualizar contador do carrinho se existir
            if (document.getElementById('cart-count')) {
                document.getElementById('cart-count').textContent = data.cartCount;
            }
        } else {
            alert('Erro ao adicionar produto ao carrinho: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar produto ao carrinho');
    });
}

// Exemplo de como modificar os botões existentes
document.addEventListener('DOMContentLoaded', function() {
    // Substituir todos os botões "Adicionar ao Carrinho"
    const botoesAdicionarCarrinho = document.querySelectorAll('.add-to-cart-btn');
    
    b