document.addEventListener('DOMContentLoaded', () => {
    // Atualizar quantidade do item
    const updateQuantityForms = document.querySelectorAll('.update-quantity');
    updateQuantityForms.forEach(form => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const productId = form.dataset.productId;
            const quantity = form.querySelector('input[name="quantity"]').value;

            fetch('../update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    productId: productId,
                    quantity: quantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload(); // Recarrega a página para mostrar o carrinho atualizado
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar quantidade');
            });
        });
    });

    // Remover item do carrinho
    const removeButtons = document.querySelectorAll('.remove-item');
    removeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.dataset.productId;

            fetch('../remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    productId: productId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload(); // Recarrega a página para mostrar o carrinho atualizado
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao remover item');
            });
        });
    });

    // Botões de ações
    const continueShoppingBtn = document.getElementById('continue-shopping');
    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', () => {
            window.location.href = '../products/'; // Ajuste para a página de produtos
        });
    }

    const finalizePurchaseBtn = document.getElementById('finalize-purchase');
    if (finalizePurchaseBtn) {
        finalizePurchaseBtn.addEventListener('click', () => {
            fetch('../finalize_purchase.php', {
                method: 'POST'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Compra finalizada com sucesso!');
                    window.location.href = '../orders/'; 
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao finalizar compra');
            });
        });
    }
});











































class CoinPaymentSystem {
    constructor() {
        this.userCoins = parseInt(document.getElementById('user-coins')?.textContent.replace(/,/g, '')) || 0;
        this.coinsPerDollar = 100;
        this.init();
    }

    init() {
        this.updateCartTotal();
        this.setupPaymentMethodListeners();
        this.setupCartUpdateListeners();
    }

    updateCartTotal() {
        const cartItems = document.querySelectorAll('.cart-item');
        let total = 0;
        
        cartItems.forEach(item => {
            const priceElement = item.querySelector('.cart-item-details p, .price');
            if (priceElement) {
                const priceText = priceElement.textContent.replace(/[R$\s]/g, '');
                const price = parseFloat(priceText.replace(',', '.'));
                if (!isNaN(price)) {
                    total += price;
                }
            }
        });
        
        this.cartTotal = total;
        this.updateCoinsRequirement();
    }

    updateCoinsRequirement() {
        const requiredCoins = Math.ceil(this.cartTotal * this.coinsPerDollar);
        const totalCoinsNeededElement = document.getElementById('total-coins-needed');
        const coinsRadio = document.getElementById('moedas');
        const coinsLabel = document.querySelector('label[for="moedas"]');
        const coinsPaymentElement = document.querySelector('.payment-method.coins');
        const insufficientFundsElement = document.getElementById('insufficient-funds');
        
        if (totalCoinsNeededElement) {
            totalCoinsNeededElement.textContent = requiredCoins.toLocaleString();
        }

        const hasSufficientCoins = this.userCoins >= requiredCoins;

        if (coinsRadio && coinsPaymentElement) {
            coinsRadio.disabled = !hasSufficientCoins;
            
            if (coinsLabel) {
                coinsLabel.className = hasSufficientCoins ? '' : 'disabled';
            }
            
            coinsPaymentElement.className = `payment-method coins ${hasSufficientCoins ? 'sufficient' : 'insufficient'}`;
            
            if (!hasSufficientCoins && coinsRadio.checked) {
                coinsRadio.checked = false;
                const firstAvailableMethod = document.querySelector('input[name="payment_method"]:not(:disabled)');
                if (firstAvailableMethod) {
                    firstAvailableMethod.checked = true;
                }
            }
        }

        if (insufficientFundsElement) {
            if (!hasSufficientCoins && requiredCoins > 0) {
                const missingCoins = requiredCoins - this.userCoins;
                insufficientFundsElement.innerHTML = `Saldo insuficiente! Você precisa de mais ${missingCoins.toLocaleString()} moedas.`;
                insufficientFundsElement.style.display = 'block';
            } else {
                insufficientFundsElement.style.display = 'none';
            }
        }

        this.updateFinalizeButton();
    }

    setupPaymentMethodListeners() {
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.updateFinalizeButton();
            });
        });
    }

    setupCartUpdateListeners() {
        const cartContainer = document.querySelector('.cart-container, .cart-items');
        if (cartContainer) {
            const observer = new MutationObserver(() => {
                setTimeout(() => {
                    this.updateCartTotal();
                }, 100);
            });
            
            observer.observe(cartContainer, {
                childList: true,
                subtree: true
            });
        }

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item') || 
                e.target.closest('.remove-item')) {
                setTimeout(() => {
                    this.updateCartTotal();
                }, 100);
            }
        });
    }

    updateFinalizeButton() {
        const finalizeButton = document.querySelector('.finalize-button, .checkout-button, button[type="submit"]');
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        
        if (!finalizeButton) return;

        if (selectedPayment && selectedPayment.value === 'moedas') {
            const requiredCoins = Math.ceil(this.cartTotal * this.coinsPerDollar);
            const hasSufficientCoins = this.userCoins >= requiredCoins;
            
            if (hasSufficientCoins) {
                finalizeButton.disabled = false;
                finalizeButton.textContent = `Pagar ${requiredCoins.toLocaleString()} Moedas`;
                finalizeButton.style.backgroundColor = '#4CAF50';
            } else {
                finalizeButton.disabled = true;
                finalizeButton.textContent = 'Saldo Insuficiente';
                finalizeButton.style.backgroundColor = '#ff4d4d';
            }
        } else if (selectedPayment) {
            finalizeButton.disabled = false;
            finalizeButton.textContent = 'Finalizar Compra';
            finalizeButton.style.backgroundColor = '';
        } else {
            finalizeButton.disabled = true;
            finalizeButton.textContent = 'Selecione um Método de Pagamento';
            finalizeButton.style.backgroundColor = '';
        }
    }

    processCoinsPayment() {
        const requiredCoins = Math.ceil(this.cartTotal * this.coinsPerDollar);
        
        if (this.userCoins < requiredCoins) {
            alert('Saldo insuficiente para completar a compra!');
            return false;
        }

        const formData = new FormData();
        formData.append('action', 'process_coins_payment');
        formData.append('coins_amount', requiredCoins);
        formData.append('cart_total', this.cartTotal);

        fetch('finalizar_compra.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showPaymentSuccess(requiredCoins);
                this.userCoins = data.new_balance;
                document.getElementById('user-coins').textContent = this.userCoins.toLocaleString();
                this.clearCart();
            } else {
                alert('Erro ao processar pagamento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar pagamento');
        });

        return true;
    }

    showPaymentSuccess(coinsSpent) {
        const paymentMethods = document.querySelector('.payment-methods');
        const successMessage = document.createElement('div');
        successMessage.className = 'coins-payment-success';
        successMessage.innerHTML = `
            <strong>✅ Pagamento realizado com sucesso!</strong><br>
            ${coinsSpent.toLocaleString()} moedas foram debitadas da sua conta.<br>
            Saldo atual: ${this.userCoins.toLocaleString()} moedas
        `;
        
        paymentMethods.appendChild(successMessage);
        
        setTimeout(() => {
            successMessage.remove();
        }, 5000);
    }

    clearCart() {
        setTimeout(() => {
            window.location.href = 'purchase_success.php';
        }, 2000);
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    const coinSystem = new CoinPaymentSystem();
    
    const checkoutForm = document.querySelector('form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            
            if (selectedPayment && selectedPayment.value === 'moedas') {
                e.preventDefault();
                coinSystem.processCoinsPayment();
            }
        });
    }
});