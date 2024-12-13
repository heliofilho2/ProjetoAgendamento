<?php
// Inicia a sessão para armazenar mensagens de erro
session_start();

require_once '../../config/Config.php'; // Inclui o config
$pdo = getDatabaseConnection();
// Definir uma variável para erro de login
$erro = "";

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se os campos foram preenchidos
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Conectar ao banco de dados e verificar as credenciais
        require_once '../../config/config.php';
        require_once '../../models/User.php';

        $userModel = new User($pdo);
        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            $erro = "Usuário não encontrado.";
        } elseif (!password_verify($senha, $user['senha'])) {
            $erro = "Senha incorreta.";
        } else {
            // Login bem-sucedido, cria a sessão do usuário
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['tenant_id'] = $user['tenant_id']; // Guarda a barbearia do usuário
            $_SESSION['tipo'] = $user['tipo'];

            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <!-- Link para o Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Seu arquivo de estilo personalizado -->
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4" style="max-width: 400px; width: 100%; border-radius: 8px;">
        <h4 class="card-title text-center mb-4">Login - Painel Administrativo</h4>

        <!-- Exibe a mensagem de erro (se houver) -->
        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>

    <!-- Script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>