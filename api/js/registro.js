function showInputs(userType, event) {
    // Garantir que temos o evento
    event = event || window.event;
    
    // Mostrar o formulário
    document.getElementById('inputFields').classList.remove('hidden');
    
    // Esconder completamente o container dos botões
    document.getElementById('buttons').classList.add('hidden');
    
    // Atualizar o valor do campo hidden para o tipo de usuário
    document.getElementById('userType').value = userType;
    
    console.log("Tipo de usuário selecionado:", userType); // Para depuração
}

// Formatação de CPF
document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})/, '$1.$2.$3-');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})/, '$1.$2.');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})/, '$1.');
            }
            
            e.target.value = value;
        });
    }

    // Formatação de telefone
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{5})/, '($1) $2-');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})/, '($1) ');
            }
            
            e.target.value = value;
        });
    }
});