<?php
// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'barbearia';

$conn = new mysqli($host, $user, $pass, $dbname);

// Verificar a conexão com o banco de dados
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Definir ID do tenant (barbearia)
$tenant_id = 1; // ID da barbearia (exemplo)

// Consulta para obter os serviços
$servicos_query = "SELECT servico_id, nome FROM servicos WHERE tenant_id = ?";
$stmt_servicos = $conn->prepare($servicos_query);
$stmt_servicos->bind_param('i', $tenant_id);
$stmt_servicos->execute();
$result_servicos = $stmt_servicos->get_result();

// Consulta para obter os barbeiros
$barbeiros_query = "SELECT user_id, nome FROM usuarios WHERE tenant_id = ? AND tipo = 'barbeiro'";
$stmt_barbeiros = $conn->prepare($barbeiros_query);
$stmt_barbeiros->bind_param('i', $tenant_id);
$stmt_barbeiros->execute();
$result_barbeiros = $stmt_barbeiros->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Serviço</title>
    <link rel="stylesheet" href="styles.css"> <!-- Estilo externo para o layout -->
</head>

<body>
    <div class="container">
        <h1>Agendar Serviço</h1>

        <!-- Formulário de agendamento -->
        <form method="POST" action="processar_agendamento.php">
            <div class="form-group">
                <label for="servico">Escolha o Serviço:</label>
                <select name="servico" id="servico" required>
                    <?php while ($servico = $result_servicos->fetch_assoc()) { ?>
                        <option value="<?php echo $servico['servico_id']; ?>">
                            <?php echo $servico['nome']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="barbeiro">Escolha o Barbeiro:</label>
                <select name="barbeiro" id="barbeiro" required>
                    <option value="">Selecione um barbeiro</option>
                    <?php while ($barbeiro = $result_barbeiros->fetch_assoc()) { ?>
                        <option value="<?php echo $barbeiro['user_id']; ?>">
                            <?php echo $barbeiro['nome']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data_agendamento">Escolha a Data:</label>
                <input type="date" name="data_agendamento" id="data_agendamento" required>
            </div>

            <div class="form-group">
                <label for="hora_agendamento">Escolha o Horário:</label>
                <select name="hora_agendamento" id="hora_agendamento" required>
                    <!-- Horários serão preenchidos dinamicamente -->
                </select>
            </div>

            <button type="submit" class="btn">Agendar</button>
        </form>
    </div>

    <!-- Script para preencher os horários disponíveis -->
    <script>
        document.getElementById('data_agendamento').addEventListener('change', function () {
            const data = this.value;
            const servico_id = document.getElementById('servico').value;
            const barbeiro_id = document.getElementById('barbeiro').value;

            if (data && servico_id && barbeiro_id) {
                fetch('verifica.php?data=' + data + '&servico_id=' + servico_id + '&barbeiro_id=' + barbeiro_id)
                    .then(response => response.json()) // Garantir que a resposta seja um JSON
                    .then(data => {
                        console.log(data); // Verifique o que está sendo retornado pelo backend
                        const horariosSelect = document.getElementById('hora_agendamento');
                        horariosSelect.innerHTML = ''; // Limpar horários anteriores

                        if (data.horarios_disponiveis && data.horarios_disponiveis.length > 0) {
                            data.horarios_disponiveis.forEach(function (horario) {
                                const option = document.createElement('option');
                                option.value = horario;
                                option.textContent = horario;
                                horariosSelect.appendChild(option);
                            });
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Nenhum horário disponível';
                            horariosSelect.appendChild(option);
                        }
                    })

                    .catch(error => {
                        console.error('Erro ao buscar horários:', error);
                    });
            } else {
                const horariosSelect = document.getElementById('hora_agendamento');
                horariosSelect.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Escolha a data e o barbeiro';
                horariosSelect.appendChild(option);
            }
        });

    </script>
</body>



<style>
    /* styles.css */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    select,
    input[type="date"] {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button.btn {
        width: 100%;
        padding: 10px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }

    button.btn:hover {
        background-color: #218838;
    }
</style>

</html>