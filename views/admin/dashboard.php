<?php
session_start();
require_once '../../controllers/AuthController.php'; // Inclui o controlador para poder usar a função de logout

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$authController = new AuthController();

// Verifica se a requisição é de logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $authController->logout();
}
?>
<?php include('../partials/header.php') ?>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include('../partials/menu.php') ?>

            <!-- Main content (Área Principal) -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-4">
                <!-- Barra Superior -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Painel de Controle</h2>
                    <div class="d-flex align-items-center">
                        <!-- Botão de Notificações -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary me-3" id="notifications-btn"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i> Notificações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="notifications-btn">
                                <li><a class="dropdown-item" href="#">Novo agendamento recebido</a></li>
                                <li><a class="dropdown-item" href="#">Usuário cadastrado</a></li>
                                <li><a class="dropdown-item" href="#">Serviço agendado</a></li>
                                <li><a class="dropdown-item" href="#">Novo comentário no serviço</a></li>
                            </ul>
                        </div>
                        <!-- Botão de Logout -->
                        <form method="POST" action="dashboard.php" class="d-inline">
                            <button type="submit" name="logout" class="btn btn-danger">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Conteúdo Principal -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumo do Sistema</h5>
                        <p class="card-text">Aqui você pode gerenciar os agendamentos, serviços e usuários da sua
                            barbearia.</p>
                        <a href="#" class="btn btn-primary">Ver Agendamentos</a>
                        <a href="#" class="btn btn-secondary">Gerenciar Serviços</a>
                    </div>
                </div>

                <!-- Notificações (Se houver) -->
                <div id="notifications" class="mt-4" style="display:none;">
                    <div class="alert alert-info">
                        <strong>Notificação:</strong> Novo agendamento recebido!
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>