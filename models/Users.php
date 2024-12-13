<?php
class Users
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Verifica se o email já existe, ignorando o próprio usuário
    private function emailExists($email, $user_id = null)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email";

        if ($user_id) {
            $sql .= " AND user_id != :user_id"; // Ignora o usuário atual
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email', $email);

        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false; // Retorna true se o email existir
    }

    // Cria um novo usuário
    public function createUser($tenant_id, $nome, $email, $senha, $tipo, $telefone, $ativo)
    {
        // Verifica se o email já existe
        if ($this->emailExists($email)) {
            throw new Exception("Email já registrado no sistema.");
        }

        // Validação de senha (mínimo 6 caracteres)
        if (strlen($senha) < 6) {
            throw new Exception("A senha deve ter no mínimo 6 caracteres.");
        }

        // Hash da senha para maior segurança
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        // Preparando a consulta SQL para inserir o novo usuário
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (tenant_id, nome, email, senha, tipo, telefone, ativo)
        VALUES (:tenant_id, :nome, :email, :senha, :tipo, :telefone, :ativo)"
        );

        // Vinculando os parâmetros
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senhaHash);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

        // Executando a consulta
        if ($stmt->execute()) {
            // Retorna o ID do usuário recém-criado
            return $this->pdo->lastInsertId();
        } else {
            throw new Exception("Erro ao cadastrar usuário.");
        }
    }


    // Obtém todos os usuários de um tenant específico, com filtragem opcional por nome e email
    // Dentro do seu modelo de Users, onde você busca os dados do usuário
    public function getUsersByTenant($tenant_id, $nome = '', $email = '')
    {
        $sql = "SELECT u.user_id, u.nome, u.email, u.tipo, u.telefone, u.ativo
            FROM usuarios u
            WHERE u.tenant_id = :tenant_id";

        if ($nome) {
            $sql .= " AND u.nome LIKE :nome";
        }
        if ($email) {
            $sql .= " AND u.email LIKE :email";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenant_id);

        if ($nome) {
            $stmt->bindValue(':nome', "%$nome%");
        }
        if ($email) {
            $stmt->bindValue(':email', "%$email%");
        }

        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agora buscamos os serviços associados a cada usuário
        foreach ($users as &$user) {
            $user['servicos'] = $this->getUserServices($user['user_id']);
        }

        return $users;
    }

    // Função para obter os serviços de um usuário
    public function getUserServices($user_id)
    {
        $sql = "SELECT s.servico_id FROM barbeiro_servicos bs
            JOIN servicos s ON bs.servico_id = s.servico_id
            WHERE bs.barbeiro_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornamos apenas os IDs dos serviços
        return array_column($services, 'servico_id');
    }


    // Obtém um usuário pelo seu ID
    public function getUserById($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna o usuário encontrado
    }

    // Atualiza um usuário existente
    public function updateUser($user_id, $nome, $email, $senha, $tipo, $telefone, $ativo)
    {
        // Se a senha foi fornecida, realiza o hash da nova senha
        if (!empty($senha)) {
            // Validação de senha (mínimo 6 caracteres)
            if (strlen($senha) < 6) {
                throw new Exception("A senha deve ter no mínimo 6 caracteres.");
            }
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        } else {
            $senhaHash = null; // Não altera a senha se não for fornecida
        }

        // Verifica se o email já existe para outro usuário (ignora o próprio usuário)
        if ($this->emailExists($email, $user_id)) {
            throw new Exception("Email já registrado por outro usuário.");
        }

        $sql = "UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo, telefone = :telefone, ativo = :ativo";

        // Se a senha for fornecida, adiciona o campo da senha na consulta SQL
        if ($senhaHash) {
            $sql .= ", senha = :senha";
        }

        $sql .= " WHERE user_id = :user_id";

        // Preparando a consulta SQL
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

        // Vincula a senha caso ela tenha sido alterada
        if ($senhaHash) {
            $stmt->bindParam(':senha', $senhaHash);
        }

        return $stmt->execute(); // Executa a atualização
    }

    // Exclui um usuário pelo ID
    public function deleteUser($user_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute(); // Executa a exclusão
    }

    // Realiza login do usuário com email e senha
    public function login($email, $senha)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            return $user; // Retorna os dados do usuário se login for bem-sucedido
        } else {
            return null; // Retorna null se falhar na autenticação
        }
    }

    // Associa um serviço a um barbeiro
    public function addServicoToBarbeiro($barbeiro_id, $servico_id, $ativo = 1)
    {
        // Verifica se o serviço já está associado ao barbeiro
        $sql = "SELECT * FROM barbeiro_servicos WHERE barbeiro_id = :barbeiro_id AND servico_id = :servico_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            throw new Exception("Este serviço já está associado a este barbeiro.");
        }

        // Se não estiver associado, insere o novo serviço
        $sql = "INSERT INTO barbeiro_servicos (barbeiro_id, servico_id, ativo) VALUES (:barbeiro_id, :servico_id, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

        return $stmt->execute(); // Retorna verdadeiro se o serviço for associado com sucesso
    }

    // Obtém os serviços de um barbeiro específico
    public function getServicosByBarbeiro($barbeiro_id)
    {
        $sql = "SELECT s.* FROM servicos s
                INNER JOIN barbeiro_servicos bs ON s.servico_id = bs.servico_id
                WHERE bs.barbeiro_id = :barbeiro_id AND bs.ativo = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos os serviços ativos para o barbeiro
    }

    // Atualiza o status de um serviço de um barbeiro (ativo ou inativo)
    public function updateServicoStatus($barbeiro_id, $servico_id, $ativo)
    {
        $sql = "UPDATE barbeiro_servicos 
                SET ativo = :ativo 
                WHERE barbeiro_id = :barbeiro_id AND servico_id = :servico_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

        return $stmt->execute(); // Retorna verdadeiro se a atualização for bem-sucedida
    }

    // Remove um serviço de um barbeiro
    public function removeServicoFromBarbeiro($barbeiro_id, $servico_id)
    {
        $sql = "DELETE FROM barbeiro_servicos WHERE barbeiro_id = :barbeiro_id AND servico_id = :servico_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':servico_id', $servico_id);

        return $stmt->execute(); // Retorna verdadeiro se o serviço for removido com sucesso
    }
    // Obtém os serviços de um usuário específico
    public function getServicosByUser($user_id)
    {
        $sql = "SELECT s.servico_id 
            FROM servicos s
            INNER JOIN barbeiro_servicos bs ON s.servico_id = bs.servico_id
            WHERE bs.barbeiro_id = :user_id AND bs.ativo = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($servico) {
            return $servico['servico_id'];
        }, $servicos);  // Retorna apenas os IDs dos serviços
    }

}

?>