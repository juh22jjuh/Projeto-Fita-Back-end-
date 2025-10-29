<?php 
// app/views/auth/reset_password.php
require_once __DIR__ . '/../templates/header.php'; 
?>

<h2>Redefinir Senha</h2>
<form action="/redefinir-senha" method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div>
        <label for="senha">Nova Senha (mínimo 8 caracteres):</label>
        <input type="password" id="senha" name="senha" required minlength="8">
    </div>
    <div>
        <label for="confirmar_senha">Confirmar Nova Senha:</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="8">
    </div>
    <button type="submit">Redefinir Senha</button>
</form>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>