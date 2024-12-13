<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/barbearia/config/config.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/barbearia/models/Users.php';


class AuthController
{
    // Função de login
    public function login($email, $senha)
    {
        // Assumindo que a variável $pdo esteja definida no config.php
        global $pdo;

        // Criação do modelo de usuário e busca pelo e-mail
        $userModel = new User($pdo);
        $user = $userModel->getUserByEmail($email);

        if ($user && password_verify($senha, $user['senha'])) {
            // Login bem-sucedido, inicia a sessão
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['tenant_id'] = $user['tenant_id']; // Guarda a barbearia do usuário
            $_SESSION['tipo'] = $user['tipo'];

            // Redireciona para o painel administrativo
            header('Location: /views/admin/dashboard.php');
            exit;
        } else {
            return "Credenciais inválidas."; // Mensagem de erro
        }
    }

    // Função de logout
    public function logout()
    {
        session_start(); // Inicia a sessão, caso não tenha sido iniciada ainda

        // Limpa os dados da sessão
        session_unset();
        session_destroy();

        // Redireciona para a página de login
        header('Location: login.php');
        exit;
    }
}
?>