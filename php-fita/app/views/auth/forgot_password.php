<?php 
// app/views/auth/forgot_password.php
require_once __DIR__ . '/../templates/header.php'; 
?>

<h2>Esqueci minha senha</h2>
<form action="/esqueci-senha" method="POST">
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <button type="submit">Enviar link de redefinição</button>
</form>
<p>Lembrou sua senha? <a href="/login">Faça Login</a></p>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>