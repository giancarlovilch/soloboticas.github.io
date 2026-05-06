const container = document.getElementById('container');
const registerBtn = document.getElementById('registerBtn');
const loginBtn = document.getElementById('loginBtn');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

function openForgotPasswordModal() {
    document.getElementById("forgotPasswordModal").style.display = "block";
}

function closeForgotPasswordModal() {
    document.getElementById("forgotPasswordModal").style.display = "none";
}