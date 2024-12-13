<?php
session_start();
require_once '../../controllers/AuthController.php'; // Inclui o controlador de autenticação
require_once '../../models/Users.php'; // Inclui o modelo Users
require_once '../../models/Services.php'; // Inclui o modelo Services

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco de dados (ajuste conforme necessário)
$pdo = getDatabaseConnection();

$usersModel = new Users($pdo);
$tenant_id = $_SESSION['tenant_id']; // Assumindo que o tenant_id seja fixo ou recuperado da sessão
$servicesModel = new Service($pdo);
$services = $servicesModel->getServicesByTenant($tenant_id);

// Variáveis de erro e sucesso
$error = '';
$success = '';

// Ação de logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $authController = new AuthController();
    $authController->logout();
}

// Filtragem de usuários
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

// Obtenção dos usuários
$users = $usersModel->getUsersByTenant($tenant_id, $nome, $email);

// Ação de cadastro de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    try {
        // Coleta os dados do formulário
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $tipo = $_POST['tipo'];
        $telefone = $_POST['telefone'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $servicos = isset($_POST['servicos']) ? $_POST['servicos'] : [];

        // Criação do novo usuário
        $user_id = $usersModel->createUser($tenant_id, $nome, $email, $senha, $tipo, $telefone, $ativo);

        // Associa os serviços ao barbeiro
        if ($tipo === 'barbeiro' && !empty($servicos)) {
            foreach ($servicos as $servico_id) {
                $stmt = $pdo->prepare("INSERT INTO barbeiro_servicos (barbeiro_id, servico_id) VALUES (:user_id, :servico_id)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':servico_id', $servico_id);
                $stmt->execute();
            }
        }

        $success = 'Usuário cadastrado com sucesso.';
        // Redireciona para a mesma página com os dados atualizados
        header('Location: usuarios.php?success=' . urlencode($success));
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}

// Ação de edição de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    try {
        // Coleta os dados do formulário
        $user_id = $_POST['user_id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $tipo = $_POST['tipo'];
        $telefone = $_POST['telefone'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        // Atualiza o usuário
        $usersModel->updateUser($user_id, $nome, $email, $senha, $tipo, $telefone, $ativo);

        // Verifica se o usuário é barbeiro e atualiza seus serviços
        if ($tipo === 'barbeiro') {
            // Apaga os serviços antigos antes de atualizar
            $stmt = $pdo->prepare("DELETE FROM barbeiro_servicos WHERE barbeiro_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Associa os novos serviços ao barbeiro
            $servicos = isset($_POST['servicos']) ? $_POST['servicos'] : [];
            foreach ($servicos as $servico_id) {
                $stmt = $pdo->prepare("INSERT INTO barbeiro_servicos (barbeiro_id, servico_id) VALUES (:user_id, :servico_id)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':servico_id', $servico_id);
                $stmt->execute();
            }
        }

        $success = 'Usuário atualizado com sucesso.';
        // Redireciona para a mesma página com os dados atualizados
        header('Location: usuarios.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}

// Ação de exclusão de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        // Coleta o ID do usuário
        $user_id = $_POST['user_id'];

        // Exclui o usuário
        $usersModel->deleteUser($user_id);
        $success = 'Usuário excluído com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: usuarios.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}
?>

<?php include('../partials/header.php') ?>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include('../partials/menu.php') ?>

            <!-- Main content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Gestão de Usuários</h4>
                </div>

                <!-- Mensagens de sucesso ou erro -->
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>

                <!-- Card de Filtro -->
                <div class="card mb-4">
                    <div class="card-header">
                        Filtros
                    </div>
                    <div class="card-body">
                        <form method="GET" action="usuarios.php">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="text" name="nome" placeholder="Filtrar por nome" class="form-control"
                                        value="<?php echo $nome; ?>" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="email" name="email" placeholder="Filtrar por email"
                                        class="form-control" value="<?php echo $email; ?>" />
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Cadastrar
                    Usuário</button>
                <!-- Card de Tabela de Usuários -->
                <div class="card">
                    <div class="card-header">
                        Usuários
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Telefone</th>
                                    <th>Ativo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo $user['nome']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td><?php echo $user['tipo']; ?></td>
                                        <td><?php echo $user['telefone']; ?></td>
                                        <td><?php echo $user['ativo'] ? 'Sim' : 'Não'; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal" data-id="<?php echo $user['user_id']; ?>"
                                                data-nome="<?php echo $user['nome']; ?>"
                                                data-email="<?php echo $user['email']; ?>"
                                                data-tipo="<?php echo $user['tipo']; ?>"
                                                data-telefone="<?php echo $user['telefone']; ?>"
                                                data-ativo="<?php echo $user['ativo']; ?>"
                                                data-servicos="<?php echo implode(',', $user['servicos']); ?>">Editar</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                data-id="<?php echo $user['user_id']; ?>">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Cadastrar Usuário -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Cadastrar Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="usuarios.php" method="POST">
                        <div class="row">
                            <!-- Coluna para os dados pessoais -->
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" name="senha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="telefone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" name="tipo">
                                    <option value="barbeiro">Barbeiro</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="gestor">Gestor</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="ativo" checked>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>

                            <!-- Coluna para os serviços -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <strong>Serviços Disponíveis</strong>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($services as $service): ?>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="servicos[]"
                                                    value="<?php echo $service['servico_id']; ?>">
                                                <label class="form-check-label"><?php echo $service['nome']; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="create_user" class="btn btn-primary">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Editar Usuário -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="usuarios.php" method="POST">
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="row">
                            <!-- Coluna para os dados pessoais -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome" id="edit_nome">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" name="senha" id="edit_senha">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="telefone" id="edit_telefone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_tipo" class="form-label">Tipo</label>
                                <select class="form-select" name="tipo" id="edit_tipo">
                                    <option value="barbeiro">Barbeiro</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="gestor">Gestor</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="ativo" id="edit_ativo">
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>

                            <!-- Coluna para os serviços -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_servicos" class="form-label">Serviços Disponíveis</label>
                                <div class="services-container" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($services as $service): ?>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="servicos[]"
                                                value="<?php echo $service['servico_id']; ?>"
                                                id="servico_<?php echo $service['servico_id']; ?>">
                                            <label class="form-check-label"
                                                for="servico_<?php echo $service['servico_id']; ?>"><?php echo $service['nome']; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_user" class="btn btn-primary">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Excluir Usuário -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Excluir Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="usuarios.php" method="POST">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <p>Tem certeza de que deseja excluir este usuário?</p>
                        <button type="submit" name="delete_user" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preenche os dados do usuário no modal de edição
        document.addEventListener('DOMContentLoaded', function () {
            const editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const email = button.getAttribute('data-email');
                const tipo = button.getAttribute('data-tipo');
                const telefone = button.getAttribute('data-telefone');
                const ativo = button.getAttribute('data-ativo');
                const servicos = button.getAttribute('data-servicos').split(',');

                const userIdField = editUserModal.querySelector('#user_id');
                const nomeField = editUserModal.querySelector('#edit_nome');
                const emailField = editUserModal.querySelector('#edit_email');
                const tipoField = editUserModal.querySelector('#edit_tipo');
                const telefoneField = editUserModal.querySelector('#edit_telefone');
                const ativoField = editUserModal.querySelector('#edit_ativo');
                const servicosFields = editUserModal.querySelectorAll('input[name="servicos[]"]'); // Todos os checkboxes

                userIdField.value = userId;
                nomeField.value = nome;
                emailField.value = email;
                tipoField.value = tipo;
                telefoneField.value = telefone;
                ativoField.checked = ativo == 1;

                // Marcar os serviços já atribuídos ao usuário
                servicosFields.forEach(function (checkbox) {
                    if (servicos.includes(checkbox.value)) {
                        checkbox.checked = true;
                    }
                });
            });
        });

    </script>

</body>

</html>