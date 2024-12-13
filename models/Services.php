<?php
class Service
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Obtém todos os serviços de um tenant específico, com filtros opcionais para nome e descrição
    public function getServicesByTenant($tenant_id, $nome = '', $descricao = '')
    {
        $sql = "SELECT * FROM servicos WHERE tenant_id = :tenant_id";

        // Aplica os filtros caso o nome ou descrição sejam fornecidos
        if ($nome) {
            $sql .= " AND nome LIKE :nome";
        }
        if ($descricao) {
            $sql .= " AND descricao LIKE :descricao";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenant_id);

        if ($nome) {
            $nome = "%" . $nome . "%"; // Adiciona as porcentagens para o LIKE
            $stmt->bindParam(':nome', $nome);
        }
        if ($descricao) {
            $descricao = "%" . $descricao . "%";
            $stmt->bindParam(':descricao', $descricao);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos os serviços encontrados
    }

    // Obtém um serviço pelo ID
    public function getServiceById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM servicos WHERE servico_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna o serviço encontrado
    }

    // Cria um novo serviço
    public function createService($tenant_id, $nome, $descricao, $duracao, $preco, $ativo)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO servicos (tenant_id, nome, descricao, duracao, preco, ativo) 
                VALUES (:tenant_id, :nome, :descricao, :duracao, :preco, :ativo)"
        );
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':duracao', $duracao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
        return $stmt->execute(); // Executa a query para inserir o serviço
    }

    // Atualiza um serviço existente
    public function updateService($id, $nome, $descricao, $duracao, $preco, $ativo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE servicos SET nome = :nome, descricao = :descricao, duracao = :duracao, preco = :preco, ativo = :ativo WHERE servico_id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':duracao', $duracao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
        return $stmt->execute(); // Executa a query para atualizar o serviço
    }

    // Exclui um serviço pelo ID
    public function deleteService($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM servicos WHERE servico_id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute(); // Executa a query para excluir o serviço
    }
}
?>