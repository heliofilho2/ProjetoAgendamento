<?php
session_start();
require_once '../../controllers/AuthController.php'; // Inclui o controlador para poder usar a função de logout
require_once '../../models/Services.php'; // Inclui o modelo de serviços

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$authController = new AuthController();

// Verifica se a requisição é de logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $authController->logout();
}

// Obtém o tenant_id da sessão
$tenant_id = $_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $duracao = $_POST['duracao'];
    $preco = $_POST['preco'];
    $ativo = isset($_POST['ativo']) ? 1 : 0; // Se não marcar, será 0 (inativo)

    // Validação simples (você pode adicionar mais validações conforme necessário)
    if (empty($nome) || empty($descricao) || empty($duracao) || empty($preco)) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        // Instancia o modelo de serviço e cadastra o novo serviço
        $serviceModel = new Service($pdo);
        $serviceModel->createService($tenant_id, $nome, $descricao, $duracao, $preco, $ativo);

        // Redireciona após o cadastro
        header('Location: servicos.php');
        exit;
    }
}

?>

<?php include('../partials/header.php') ?>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Menu Lateral) -->
            <?php include('../partials/menu.php') ?>

            <!-- Main content (Área Principal) -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <!-- Barra Superior -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Cadastrar novo serviço</h4>
                    <div class="d-flex align-items-center">
                        <form method="POST" action="servicos.php">
                            <button type="submit" name="logout" class="btn btn-danger">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Formulário de Cadastro de Serviço -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Preencha os dados do serviço</h5>
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="cadastrar_servico.php">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Serviço</label>
                                <input type="text" name="nome" id="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <input type="text" name="descricao" id="descricao" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="duracao" class="form-label">Duração (minutos)</label>
                                <input type="number" name="duracao" id="duracao" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço (R$)</label>
                                <input type="text" name="preco" id="preco" class="form-control" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="ativo" id="ativo" checked>
                                <label class="form-check-label" for="ativo">Serviço Ativo</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Cadastrar Serviço</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>