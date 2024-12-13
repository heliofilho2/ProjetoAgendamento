<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>

    <!-- Link para o Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Faz o menu ocupar toda a altura da tela */
        #sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 250px;
            height: 100%;
            background-color: #343a40;
        }

        /* Ajusta o conteúdo para não ficar sobrepondo o sidebar */
        .main-content {
            margin-left: 250px;
        }

        /* Estilo para os cards */
        .card-body {
            min-height: 150px;
        }

        /* Ajuste da altura do botão de notificações */
        #notifications-btn {
            position: relative;
        }

        /* Dropdown de notificações */
        .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>