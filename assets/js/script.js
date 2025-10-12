
function getUsers() {
    const stored = localStorage.getItem('users');
    if (!stored) {
        localStorage.setItem('users', JSON.stringify([]));
        return [];
    }
    return JSON.parse(stored);
}

function saveUsers(users) {
    localStorage.setItem('users', JSON.stringify(users));
}

function toggleForms() {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    if (loginForm.style.display === "none") {
        loginForm.style.display = "block";
        registerForm.style.display = "none";
        clearAlerts();
    } else {
        loginForm.style.display = "none";
        registerForm.style.display = "block";
        clearAlerts();
    }
}

function showAlert(elementId, message, type) {
    const alert = document.getElementById(elementId);
    alert.textContent = message;
    alert.className = `alert alert-${type} show`;

    setTimeout(() => {
        alert.classList.remove('show');
    }, 5000);
}

function clearAlerts() {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.classList.remove('show');
    });
    document.querySelectorAll('input, select').forEach(input => {
        input.classList.remove('error-input');
    });
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function checkPasswordStrength() {
    const password = document.getElementById("registerPassword").value;
    const strengthDiv = document.getElementById("passwordStrength");

    if (password.length === 0) {
        strengthDiv.classList.remove('show');
        return;
    }

    strengthDiv.classList.add('show');

    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    if (strength <= 1) {
        strengthDiv.textContent = "⚠️ Contraseña débil";
        strengthDiv.className = "password-strength show strength-weak";
    } else if (strength <= 2) {
        strengthDiv.textContent = "⚡ Contraseña media";
        strengthDiv.className = "password-strength show strength-medium";
    } else {
        strengthDiv.textContent = "✓ Contraseña fuerte";
        strengthDiv.className = "password-strength show strength-strong";
    }
}

async function login() {
  const correo = document.getElementById('loginEmail').value;
  const contrasena = document.getElementById('loginPassword').value;
  const alert = document.getElementById('loginAlert');

  if (!correo || !contrasena) {
    alert.innerHTML = "Completa todos los campos.";
    return;
  }

  const data = new URLSearchParams();
  data.append("correo", correo);
  data.append("contrasena", contrasena);

  const response = await fetch('php/login.php', {
    method: 'POST',
    body: data
  });

  const result = await response.text();

  if (result === "ok") {
    alert.innerHTML = "Acceso exitoso. Redirigiendo...";
    // Redirige a tu página principal
    window.location.href = "dashboard.html";
  } else if (result === "incorrecto") {
    alert.innerHTML = "Contraseña incorrecta.";
  } else {
    alert.innerHTML = "Usuario no encontrado.";
  }
}

function register() {
    clearAlerts();

    const name = document.getElementById("registerName").value.trim();
    const email = document.getElementById("registerEmail").value.trim();
    const password = document.getElementById("registerPassword").value.trim();

    if (!name || !email || !password) {
        showAlert("registerAlert", "❌ Por favor, completa todos los campos", "error");
        if (!name) document.getElementById("registerName").classList.add('error-input');
        if (!email) document.getElementById("registerEmail").classList.add('error-input');
        if (!password) document.getElementById("registerPassword").classList.add('error-input');
        return;
    }

    if (name.length < 3) {
        showAlert("registerAlert", "❌ El nombre debe tener al menos 3 caracteres", "error");
        document.getElementById("registerName").classList.add('error-input');
        return;
    }

    if (!validateEmail(email)) {
        showAlert("registerAlert", "❌ Por favor, ingresa un correo válido", "error");
        document.getElementById("registerEmail").classList.add('error-input');
        return;
    }

    if (password.length < 8) {
        showAlert("registerAlert", "❌ La contraseña debe tener al menos 8 caracteres", "error");
        document.getElementById("registerPassword").classList.add('error-input');
        return;
    }

    const users = getUsers();

    if (users.find(u => u.email === email)) {
        showAlert("registerAlert", "❌ Este correo ya está registrado", "error");
        document.getElementById("registerEmail").classList.add('error-input');
        return;
    }

    users.push({ name, email, password, role: "usuario" });
    saveUsers(users);

    showAlert("registerAlert", "✓ Cuenta creada exitosamente. Redirigiendo al login...", "success");

    setTimeout(() => {
        toggleForms();
        document.getElementById("loginEmail").value = email;
    }, 1500);
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('loginPassword').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') login();
    });

    document.getElementById('registerPassword').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') register();
    });
});












function toggleForms() {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
  registerForm.style.display = registerForm.style.display === 'none' ? 'block' : 'none';
}

async function register() {
  const nombre = document.getElementById('registerName').value;
  const correo = document.getElementById('registerEmail').value;
  const contrasena = document.getElementById('registerPassword').value;
  const alert = document.getElementById('registerAlert');

  if (!nombre || !correo || !contrasena) {
    alert.innerHTML = "Completa todos los campos.";
    return;
  }

  const data = new URLSearchParams();
  data.append("nombre", nombre);
  data.append("correo", correo);
  data.append("contrasena", contrasena);

  const response = await fetch('php/registro.php', {
    method: 'POST',
    body: data
  });

  const result = await response.text();

  if (result === "ok") {
    alert.innerHTML = "Registro exitoso. Ahora inicia sesión.";
    toggleForms();
  } else if (result === "existe") {
    alert.innerHTML = "El correo ya está registrado.";
  } else {
    alert.innerHTML = "Error al registrarse.";
  }
}

