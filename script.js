// Captura o ícone e o campo de senha
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#senha');

// Escuta o evento de clique no ícone do olhinho
togglePassword.addEventListener('click', function () {
    // Verifica qual é o tipo atual do campo e inverte
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    
    // Troca o ícone do olho (aberto para fechado)
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});