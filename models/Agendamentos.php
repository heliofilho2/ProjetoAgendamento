<?php
class Agendamento
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Obtém todos os agendamentos de um tenant específico, com filtros opcionais para nome do cliente e data
    public function getAgendamentosByTenant($tenant_id, $cliente_nome = '', $data_inicio = '', $data_fim = '')
    {
        $sql = "SELECT a.agendamento_id, a.tenant_id, a.cliente_id, a.servico_id, a.barbeiro_id, 
                a.data_agendamento, a.status, a.observacoes,
                DATE_FORMAT(a.data_agendamento, '%H:%i') AS hora_agendamento, 
                u.nome AS cliente_nome, 
                s.nome AS servico_nome,
                b.nome AS barbeiro_nome
        FROM agendamentos a
        JOIN usuarios u ON a.cliente_id = u.user_id AND u.tipo = 'cliente'
        JOIN servicos s ON a.servico_id = s.servico_id
        JOIN usuarios b ON a.barbeiro_id = b.user_id AND b.tipo = 'barbeiro'
        WHERE a.tenant_id = :tenant_id";

        // Aplica os filtros caso o nome do cliente ou as datas sejam fornecidas
        if ($cliente_nome) {
            $sql .= " AND u.nome LIKE :cliente_nome";
        }
        if ($data_inicio && $data_fim) {
            $sql .= " AND data_agendamento BETWEEN :data_inicio AND :data_fim";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenant_id);

        if ($cliente_nome) {
            $cliente_nome = "%" . $cliente_nome . "%"; // Adiciona as porcentagens para o LIKE
            $stmt->bindParam(':cliente_nome', $cliente_nome);
        }
        if ($data_inicio && $data_fim) {
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos os agendamentos encontrados
    }

    // Obtém um agendamento pelo ID
    public function getAgendamentoById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM agendamentos WHERE agendamento_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna o agendamento encontrado
    }

    // Cria um novo agendamento
    public function createAgendamento($tenant_id, $cliente_id, $servico_id, $barbeiro_id, $data_agendamento, $status = 'pendente', $observacoes = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO agendamentos (tenant_id, cliente_id, servico_id, barbeiro_id, data_agendamento, status, observacoes) 
                VALUES (:tenant_id, :cliente_id, :servico_id, :barbeiro_id, :data_agendamento, :status, :observacoes)"
        );
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':data_agendamento', $data_agendamento);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':observacoes', $observacoes);
        return $stmt->execute(); // Executa a query para inserir o agendamento
    }

    // Atualiza um agendamento existente
    public function updateAgendamento($id, $cliente_id, $servico_id, $barbeiro_id, $data_agendamento, $status, $observacoes)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE agendamentos SET cliente_id = :cliente_id, servico_id = :servico_id, barbeiro_id = :barbeiro_id, 
                                     data_agendamento = :data_agendamento, status = :status, observacoes = :observacoes 
             WHERE agendamento_id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->bindParam(':data_agendamento', $data_agendamento);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':observacoes', $observacoes);
        return $stmt->execute(); // Executa a query para atualizar o agendamento
    }

    // Exclui um agendamento pelo ID
    public function deleteAgendamento($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM agendamentos WHERE agendamento_id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute(); // Executa a query para excluir o agendamento
    }

    // Obtém todos os agendamentos de um serviço específico
    public function getAgendamentosByServico($servico_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM agendamentos WHERE servico_id = :servico_id");
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os agendamentos para o serviço
    }

    // Obtém os agendamentos de um barbeiro específico
    public function getAgendamentosByBarbeiro($barbeiro_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM agendamentos WHERE barbeiro_id = :barbeiro_id");
        $stmt->bindParam(':barbeiro_id', $barbeiro_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os agendamentos para o barbeiro
    }

    // Obtém os agendamentos com status específico (ex: 'confirmado', 'pendente')
    public function getAgendamentosByStatus($tenant_id, $status)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM agendamentos WHERE tenant_id = :tenant_id AND status = :status");
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os agendamentos para o status especificado
    }
}
?>