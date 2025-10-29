<?php
// app/controllers/AuthController.php

require_once 'BaseController.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct(PDO $pdo) {
        parent::__construct($pdo); // Chama o construtor do BaseController
        $this->userModel = new User($this->pdo); // Instancia o Model
    }

    // Ação: Mostrar a View de login
    public function showLogin() {
        $this->view('auth/login');
    }
  //Mostrar formulário de esqueci a senha
    public function showForgotPassword() {
        $this->view('auth/forgot_password');
    }

    //Processar solicitação de redefinição de senha
      public function doForgotPassword() {
        $email = trim($_POST['email']);

        // Verifica se o usuário existe
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Gera token único
            $token = bin2hex(random_bytes(50));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira em 1 hora

            // Salva token no banco
            $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            // Em um sistema real, aqui enviaria o email
            // Por enquanto, vamos simular mostrando o link
            $resetLink = "http://localhost/redefinir-senha?token=" . $token;
            
            $_SESSION['info_message'] = "Link de redefinição gerado: <a href='$resetLink'>$resetLink</a> (Em produção, isto seria enviado por email)";
        } else {
            // Por segurança, não revelamos se o email existe ou não
            $_SESSION['info_message'] = "Se o email existir, enviaremos instruções para redefinir sua senha.";
        }

        $this->redirect('/esqueci-senha');
    }

    //Mostrar formulário para redefinir senha
    public function showResetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error_message'] = "Token inválido.";
            $this->redirect('/esqueci-senha');
        }

        // Verifica se o token é válido e não expirou
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            $_SESSION['error_message'] = "Token inválido ou expirado.";
            $this->redirect('/esqueci-senha');
        }

        $this->view('auth/reset_password', ['token' => $token]);
    }


    //Processar redefinição de senha
    public function doResetPassword() {
        $token = $_POST['token'] ?? '';
        $senha = trim($_POST['senha']);
        $confirmar_senha = trim($_POST['confirmar_senha']);

        // Validações
        if (strlen($senha) < 8) {
            $_SESSION['error_message'] = "A senha deve ter no mínimo 8 caracteres.";
            $this->redirect('/redefinir-senha?token=' . $token);
        }

        if ($senha !== $confirmar_senha) {
            $_SESSION['error_message'] = "As senhas não coincidem.";
            $this->redirect('/redefinir-senha?token=' . $token);
        }

        // Verifica token válido
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            $_SESSION['error_message'] = "Token inválido ou expirado.";
            $this->redirect('/esqueci-senha');
        }

        // Atualiza senha do usuário
        $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET senha = ? WHERE email = ?");
        
        if ($stmt->execute([$hash_senha, $resetRequest['email']])) {
            // Remove o token usado
            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $_SESSION['success_message'] = "Senha redefinida com sucesso! Faça login com sua nova senha.";
            $this->redirect('/login');
        } else {
            $_SESSION['error_message'] = "Erro ao redefinir senha. Tente novamente.";
            $this->redirect('/redefinir-senha?token=' . $token);
        }
    
    }

    // Ação: Processar o POST do login
    public function doLogin() {
        $email = trim($_POST['email']);
        $senha_digitada = trim($_POST['senha']);

        $user = $this->userModel->findByEmail($email);

        // Regra de Negócio: Bloqueio de 3 tentativas (requer DB/sessão) - NÃO IMPLEMENTADO (complexo)
        
        // Verifica se o usuário existe e a senha está correta
        if ($user && password_verify($senha_digitada, $user['senha'])) {
            // Sucesso: Salva dados na sessão
            session_regenerate_id(true); // Segurança
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['perfil_completo'] = (int)$user['perfil_completo'];

            // Regra de Negócio: Perfil completo obrigatório
            if ($_SESSION['perfil_completo'] == 0) {
                $this->redirect('/perfil/completar');
            }

            // Perfil completo, redireciona para o dashboard
            $this->redirect('/dashboard');
        } else {
            // Falha
            $_SESSION['error_message'] = "Email ou senha incorretos.";
            $this->redirect('/login');
        }
    }

    // Ação: Mostrar View de cadastro
    public function showRegister() {
        $this->view('auth/cadastro');
    }

    // Ação: Processar o POST do cadastro
    public function doRegister() {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);
        $confirmar_senha = trim($_POST['confirmar_senha']);

        // Regras de Negócio
        if (strlen($senha) < 8) {
            $_SESSION['error_message'] = "A senha deve ter no mínimo 8 caracteres.";
            $this->redirect('/cadastro');
        }
        if ($senha !== $confirmar_senha) {
            $_SESSION['error_message'] = "As senhas não coincidem.";
            $this->redirect('/cadastro');
        }

        $hash_senha = password_hash($senha, PASSWORD_DEFAULT);

        // Tenta criar o usuário
        if ($this->userModel->create($nome, $email, $hash_senha)) {
            $_SESSION['success_message'] = "Cadastro realizado com sucesso! Faça o login.";
            $this->redirect('/login');
        } else {
            // Regra de Negócio: Email único
            $_SESSION['error_message'] = "Este email já está cadastrado.";
            $this->redirect('/cadastro');
        }
    }

    // Ação: Logout
    public function logout() {
        session_unset();
        session_destroy();
        session_start(); // Reinicia para a mensagem de sucesso
        $_SESSION['success_message'] = "Você saiu com sucesso.";
        $this->redirect('/login');
    }
}
?>