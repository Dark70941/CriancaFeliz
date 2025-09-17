document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const forgotPasswordLink = document.getElementById('forgotPassword');
    const loginBtn = document.querySelector('.login-btn');

    // Validação em tempo real dos campos
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function updateButtonState() {
        const isEmailValid = validateEmail(emailInput.value);
        const isPasswordValid = validatePassword(passwordInput.value);
        
        if (isEmailValid && isPasswordValid) {
            loginBtn.style.opacity = '1';
            loginBtn.disabled = false;
        } else {
            loginBtn.style.opacity = '0.7';
            loginBtn.disabled = true;
        }
    }

    // Event listeners para validação em tempo real
    emailInput.addEventListener('input', function() {
        const isValid = validateEmail(this.value);
        if (this.value.length > 0) {
            if (isValid) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        } else {
            this.style.borderColor = '#e0e0e0';
        }
        updateButtonState();
    });

    passwordInput.addEventListener('input', function() {
        const isValid = validatePassword(this.value);
        if (this.value.length > 0) {
            if (isValid) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        } else {
            this.style.borderColor = '#e0e0e0';
        }
        updateButtonState();
    });

    // Submissão do formulário
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // Validação final
        if (!validateEmail(email)) {
            showMessage('Por favor, insira um email válido.', 'error');
            emailInput.focus();
            return;
        }

        if (!validatePassword(password)) {
            showMessage('A senha deve ter pelo menos 6 caracteres.', 'error');
            passwordInput.focus();
            return;
        }

        // Animação de loading no botão
        loginBtn.innerHTML = '<span>Entrando...</span>';
        loginBtn.disabled = true;

        // Simular delay de processamento
        setTimeout(() => {
            // Aqui você pode fazer a requisição AJAX para o servidor
            // Por enquanto, vamos apenas submeter o formulário normalmente
            this.submit();
        }, 1000);
    });

    // Link "Esqueceu a senha?"
    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        showMessage('Funcionalidade de recuperação de senha em desenvolvimento.', 'info');
    });

    // Função para mostrar mensagens
    function showMessage(message, type) {
        // Remove mensagem anterior se existir
        const existingMessage = document.querySelector('.message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        
        // Estilos da mensagem
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;

        if (type === 'error') {
            messageDiv.style.backgroundColor = '#e74c3c';
        } else if (type === 'success') {
            messageDiv.style.backgroundColor = '#27ae60';
        } else if (type === 'info') {
            messageDiv.style.backgroundColor = '#3498db';
        }

        document.body.appendChild(messageDiv);

        // Remove a mensagem após 4 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }
        }, 4000);
    }

    // Adicionar animações CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Estado inicial do botão
    updateButtonState();

    // Efeito de foco suave nos inputs
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});
