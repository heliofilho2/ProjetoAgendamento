-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/12/2024 às 02:18
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `barbearia`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `agendamento_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `barbeiro_id` int(11) NOT NULL,
  `data_agendamento` datetime NOT NULL,
  `status` enum('pendente','confirmado','cancelado','concluido') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `pago` tinyint(1) DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`agendamento_id`, `tenant_id`, `cliente_id`, `servico_id`, `barbeiro_id`, `data_agendamento`, `status`, `observacoes`, `pago`, `data_pagamento`, `created_at`, `updated_at`) VALUES
(14, 1, 14, 8, 13, '2024-12-16 08:30:00', 'confirmado', 'xurasgou', 1, '2024-12-12', '2024-12-12 15:24:48', '2024-12-13 00:36:30'),
(15, 1, 14, 8, 13, '2024-12-16 13:30:00', 'confirmado', 'xurastei', 1, '2024-12-12', '2024-12-12 15:29:09', '2024-12-13 00:36:36'),
(16, 1, 14, 8, 12, '2024-12-16 17:40:00', 'confirmado', NULL, 1, '2024-12-12', '2024-12-12 18:29:55', '2024-12-13 00:36:39'),
(17, 1, 14, 13, 12, '2024-12-16 11:30:00', 'cancelado', NULL, 1, '2024-12-12', '2024-12-13 00:19:02', '2024-12-13 00:52:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `barbearias`
--

CREATE TABLE `barbearias` (
  `tenant_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `liberar_agenda` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `barbearias`
--

INSERT INTO `barbearias` (`tenant_id`, `nome`, `endereco`, `telefone`, `email`, `logo`, `liberar_agenda`, `created_at`, `updated_at`) VALUES
(1, 'barbearia teste', 'rua A', '35991546122', 'contato@barbeariateste.com', NULL, 0, '2024-12-06 15:12:29', '2024-12-06 15:12:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `barbeiro_indisponibilidade`
--

CREATE TABLE `barbeiro_indisponibilidade` (
  `id` int(11) NOT NULL,
  `barbeiro_id` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `barbeiro_indisponibilidade`
--

INSERT INTO `barbeiro_indisponibilidade` (`id`, `barbeiro_id`, `data_inicio`, `data_fim`) VALUES
(2, 12, '2024-12-16 13:30:00', '2024-12-16 14:20:00'),
(3, 13, '2024-12-16 08:20:00', '2024-12-16 18:30:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `barbeiro_servicos`
--

CREATE TABLE `barbeiro_servicos` (
  `id` int(11) NOT NULL,
  `barbeiro_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `barbeiro_servicos`
--

INSERT INTO `barbeiro_servicos` (`id`, `barbeiro_id`, `servico_id`, `ativo`) VALUES
(24, 12, 5, 1),
(27, 13, 5, 1),
(28, 13, 7, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `horarios_funcionamento`
--

CREATE TABLE `horarios_funcionamento` (
  `horario_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `dia_semana` enum('segunda','terça','quarta','quinta','sexta','sábado','domingo') NOT NULL,
  `hora_abertura` time NOT NULL,
  `hora_fechamento` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `horarios_funcionamento`
--

INSERT INTO `horarios_funcionamento` (`horario_id`, `tenant_id`, `dia_semana`, `hora_abertura`, `hora_fechamento`) VALUES
(1, 1, 'segunda', '08:30:00', '18:30:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `servico_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `duracao` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`servico_id`, `tenant_id`, `nome`, `descricao`, `duracao`, `preco`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Corte + barba', 'Corte de cabelo e barba', 30, 25.00, 1, '2024-12-06 19:19:27', '2024-12-09 15:04:06'),
(5, 1, 'Sobrancelha', 'Sobrancelha zica', 25, 30.00, 1, '2024-12-09 19:59:25', '2024-12-09 19:59:25'),
(6, 1, 'Luzes', 'Nevouuuuuuuu', 60, 50.00, 1, '2024-12-10 14:55:24', '2024-12-10 14:55:24'),
(7, 1, 'Tranças', 'Trançouuuu', 120, 80.00, 1, '2024-12-10 14:55:39', '2024-12-10 14:55:39'),
(8, 1, 'Barba', 'Somente barba', 50, 15.00, 1, '2024-12-10 15:00:27', '2024-12-10 18:32:54'),
(13, 1, 'Cabelo', 'Cabelo do furico', 30, 30.00, 1, '2024-12-13 00:16:54', '2024-12-13 00:16:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `user_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('gestor','cliente','barbeiro') NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`user_id`, `tenant_id`, `nome`, `email`, `senha`, `tipo`, `telefone`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'thiago silva', 'admin@admin.com', '$2y$10$amSZny7ezo51aW.EWPefp.eWK/j6O71TxghHHE2tRKQdbck40.bHi', 'gestor', '35991546122', 1, '2024-12-06 15:13:17', '2024-12-09 18:44:55'),
(12, 1, 'helio filho', 'helio-filho@gmail.com', '$2y$10$F/hNcf9MRM91l3/PyJkq4O9pbj0L2h/YINDwO0RiOHdlV36WW.kHu', 'barbeiro', '35987050229', 1, '2024-12-09 20:04:53', '2024-12-09 20:05:57'),
(13, 1, 'thiaguinho', 'thiago@gmail.com', '$2y$10$UartnJQw3fRnTfktgH/Oq.n8ii54kc/nVcIab.GCofr8OyPAbrj7m', 'barbeiro', '35991546122', 1, '2024-12-09 20:24:20', '2024-12-09 20:24:20'),
(14, 1, 'Usuario teste', 'teste@gmail.com', '$2y$10$A7ogUEe8bvpaQb0EsxFv7OR.o4uGaNr5bDqp6VdBynXh8RXbvlA1O', 'cliente', '35991546122', 1, '2024-12-10 15:17:15', '2024-12-12 17:20:21');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`agendamento_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Índices de tabela `barbearias`
--
ALTER TABLE `barbearias`
  ADD PRIMARY KEY (`tenant_id`);

--
-- Índices de tabela `barbeiro_indisponibilidade`
--
ALTER TABLE `barbeiro_indisponibilidade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barbeiro_id` (`barbeiro_id`);

--
-- Índices de tabela `barbeiro_servicos`
--
ALTER TABLE `barbeiro_servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servico_id` (`servico_id`),
  ADD KEY `barbeiro_id` (`barbeiro_id`,`servico_id`) USING BTREE;

--
-- Índices de tabela `horarios_funcionamento`
--
ALTER TABLE `horarios_funcionamento`
  ADD PRIMARY KEY (`horario_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`servico_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `agendamento_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `barbearias`
--
ALTER TABLE `barbearias`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `barbeiro_indisponibilidade`
--
ALTER TABLE `barbeiro_indisponibilidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `barbeiro_servicos`
--
ALTER TABLE `barbeiro_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `horarios_funcionamento`
--
ALTER TABLE `horarios_funcionamento`
  MODIFY `horario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `servico_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `barbearias` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`servico_id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `barbeiro_indisponibilidade`
--
ALTER TABLE `barbeiro_indisponibilidade`
  ADD CONSTRAINT `barbeiro_indisponibilidade_ibfk_1` FOREIGN KEY (`barbeiro_id`) REFERENCES `usuarios` (`user_id`);

--
-- Restrições para tabelas `barbeiro_servicos`
--
ALTER TABLE `barbeiro_servicos`
  ADD CONSTRAINT `barbeiro_servicos_ibfk_1` FOREIGN KEY (`barbeiro_id`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `barbeiro_servicos_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`servico_id`);

--
-- Restrições para tabelas `horarios_funcionamento`
--
ALTER TABLE `horarios_funcionamento`
  ADD CONSTRAINT `horarios_funcionamento_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `barbearias` (`tenant_id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `servicos`
--
ALTER TABLE `servicos`
  ADD CONSTRAINT `servicos_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `barbearias` (`tenant_id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `barbearias` (`tenant_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
