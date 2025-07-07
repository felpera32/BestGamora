-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/07/2025 às 04:57
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
-- Banco de dados: `gamoraloja`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `idCliente` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `moedas` int(11) DEFAULT 0,
  `cpf` varchar(14) DEFAULT NULL,
  `dataCadastro` datetime DEFAULT current_timestamp(),
  `ultimoAcesso` datetime DEFAULT NULL,
  `status` enum('Ativo','Inativo','Bloqueado') DEFAULT 'Ativo',
  `tipo_usuario` enum('comprador','vendedor') NOT NULL DEFAULT 'comprador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`idCliente`, `nome`, `email`, `telefone`, `senha_hash`, `moedas`, `cpf`, `dataCadastro`, `ultimoAcesso`, `status`, `tipo_usuario`) VALUES
(1, 'pedro', 'admin@gmail.com', '312312312', '$2y$10$jwuWP4rqJUPr56dzyDUtDefhbOZraxD/SzelzaDLI/5ieZEgXrF9O', 1224, '12312312312', '2025-03-27 18:09:41', '2025-04-06 21:34:07', 'Ativo', 'comprador'),
(9, 'vendedor', 'vendedor@gmail.com', '(41) 23412-4314', '$2y$10$VohU61nWJ2crS8H6eJ6UyOfKOgf1tgeiM9FkqWB7oh4NTU/CJ97ae', 0, '32131231232', '2025-04-09 21:04:51', NULL, 'Ativo', 'vendedor'),
(10, 'Joaopedro', 'joao@gmail.com', '(12) 31354-1654', '$2y$10$gtROP6yObKxoT8n9HDQ4/.0rXUeOrwW/yyewc.N8wDGOvgza0LLBO', 541, '12345678951', '2025-07-01 02:53:55', NULL, 'Ativo', 'comprador'),
(11, 'blabla', 'bla@gmail.com', '(43) 21413-4165', '$2y$10$aGN6xp2DWdok2B3BpTi2yujWHkimNrJgmBAVFmZ941XTV89F9glVG', 0, '43214312412', '2025-07-01 19:36:55', NULL, 'Ativo', 'comprador'),
(12, 'hm', 'aoba@gmail.com', '(99) 99999-9999', '$2y$10$6TFf7LVDo/4.djoAWfZhFOshwNJKRn3rDEx0YsLu4K0hfs.eVTbFm', 0, '99999999999', '2025-07-01 20:23:52', NULL, 'Ativo', 'vendedor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagensproduto`
--

CREATE TABLE `imagensproduto` (
  `idImagemProduto` int(11) NOT NULL,
  `idProduto` int(11) NOT NULL,
  `urlImagem` varchar(255) NOT NULL,
  `ordemExibicao` int(11) DEFAULT 1,
  `descricaoImagem` varchar(200) DEFAULT NULL,
  `dataCadastro` datetime DEFAULT current_timestamp(),
  `status` enum('Ativa','Inativa') DEFAULT 'Ativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagensproduto`
--

INSERT INTO `imagensproduto` (`idImagemProduto`, `idProduto`, `urlImagem`, `ordemExibicao`, `descricaoImagem`, `dataCadastro`, `status`) VALUES
(1, 1, 'src/capas/stardew/capa.avif', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(2, 2, 'src/capas/sekiro/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(3, 3, 'src/capas/moonlighter/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(4, 4, 'src/capas/civ/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(5, 5, 'src/capas/bg3/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(6, 6, 'src/capas/diver/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(7, 7, 'src/capas/celeste/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(8, 8, 'src/capas/spiderman/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(9, 9, 'src/capas/stardew/capa.avif', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(10, 10, 'src/capas/moonlighter/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(11, 11, 'src/capas/diver/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(12, 12, 'src/capas/sekiro/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(15, 15, 'src/capas/monster/capa.webp', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(16, 16, 'src/capas/moonlighter/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(18, 18, 'src/capas/celeste/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(19, 19, 'src/capas/Wukong/capa.avif', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(21, 21, 'src/capas/reddead/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(22, 22, 'src/capas/gta/capa.jpg', 1, NULL, '2025-03-31 21:54:11', 'Ativa'),
(23, 16, 'src/Capas/EnigmaDoMedo/capa.jpg', 1, NULL, '2025-06-29 20:02:30', 'Ativa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `idItemVenda` int(11) NOT NULL,
  `idVenda` int(11) NOT NULL,
  `idProduto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `precoUnitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_venda`
--

INSERT INTO `itens_venda` (`idItemVenda`, `idVenda`, `idProduto`, `quantidade`, `precoUnitario`) VALUES
(1, 7, 1, 1, 129.00),
(2, 8, 1, 1, 129.00),
(3, 9, 1, 1, 129.00),
(4, 10, 1, 1, 129.00),
(5, 11, 1, 1, 129.00),
(6, 12, 1, 1, 129.00),
(7, 13, 4, 1, 129.00),
(8, 14, 4, 1, 129.00),
(9, 15, 4, 1, 129.00),
(10, 16, 4, 1, 129.00),
(11, 17, 4, 1, 129.00),
(12, 18, 4, 1, 129.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `idProduto` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL,
  `imagemPrincipal` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `dataCadastro` datetime DEFAULT current_timestamp(),
  `fabricante` varchar(100) DEFAULT NULL,
  `peso` decimal(6,2) DEFAULT NULL,
  `dimensoes` varchar(50) DEFAULT NULL,
  `status` enum('Disponível','Esgotado','Descontinuado') DEFAULT 'Disponível'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`idProduto`, `nome`, `categoria`, `preco`, `estoque`, `imagemPrincipal`, `descricao`, `dataCadastro`, `fabricante`, `peso`, `dimensoes`, `status`) VALUES
(1, 'Stardew Valley', 'Jogos PC', 24.99, 100, 'stardew-valley.jpg', 'Jogo de simulação de fazenda', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(2, 'Sekiro', 'Jogos PC', 274.00, 20, 'sekiro.jpg', 'Jogo de ação e combate da FromSoftware', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(3, 'Moonlighter', 'Jogos PC', 39.00, 50, 'moonlighter.jpg', 'Jogo de RPG e gerenciamento de loja', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(4, 'Civilizations 6', 'Jogos PC', 129.00, 40, 'civilization-6.jpg', 'Jogo de estratégia de civilizações', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(5, 'Baldur\'s Gate 3', 'Jogos PC', 199.99, 35, 'baldurs-gate-3.jpg', 'RPG de fantasia baseado em Dungeons & Dragons', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(6, 'Dave The Diver', 'Jogos PC', 59.00, 60, 'dave-the-diver.jpg', 'Jogo de aventura e gerenciamento submarino', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(7, 'Celeste', 'Jogos PC', 40.00, 75, 'celeste.jpg', 'Jogo de plataforma e aventura indie', '2025-03-27 12:31:01', NULL, NULL, NULL, 'Disponível'),
(8, 'Spider-Man 2', 'Jogos', 199.00, 100, NULL, NULL, '2025-03-27 16:37:43', 'Contact', NULL, NULL, 'Disponível'),
(9, 'Stardew Valley', 'Jogos', 24.99, 50, NULL, NULL, '2025-03-27 16:37:43', 'Schwarz', NULL, NULL, 'Disponível'),
(10, 'Moonlighter', 'Jogos', 39.00, 30, NULL, NULL, '2025-03-27 16:37:43', 'Compass', NULL, NULL, 'Disponível'),
(11, 'Dave The Diver', 'Jogos', 58.00, 40, NULL, NULL, '2025-03-27 16:37:43', 'Compass', NULL, NULL, 'Disponível'),
(12, 'Sekiro', 'Jogos', 274.00, 25, NULL, NULL, '2025-03-27 16:37:43', 'Compass', NULL, NULL, 'Disponível'),
(15, 'Monster Hunter Wilds', 'Jogos', 219.00, 80, NULL, NULL, '2025-03-27 16:41:12', NULL, NULL, NULL, 'Disponível'),
(16, 'Enigma do Medo', 'Jogos', 49.50, 35, NULL, NULL, '2025-03-27 16:41:12', NULL, NULL, NULL, 'Disponível'),
(18, 'Celeste', 'Jogos', 40.00, 45, NULL, NULL, '2025-03-27 16:41:12', NULL, NULL, NULL, 'Disponível'),
(19, 'Black Myth: Wukong', 'Jogos', 199.99, 30, NULL, NULL, '2025-03-27 16:41:12', NULL, NULL, NULL, 'Disponível'),
(21, 'Red Dead Redemption 2', 'Jogos', 299.00, 50, 'red-dead-redemption-2.jpg', 'Jogo de mundo aberto no velho oeste', '2025-03-29 17:17:36', NULL, NULL, NULL, 'Disponível'),
(22, 'GTA 6', 'Jogos', 10000.00, 20, 'gta-6.jpg', 'Jogo de mundo aberto da Rockstar Games', '2025-03-29 17:22:40', 'Rockstar Games', NULL, NULL, 'Disponível'),
(29, 'greawe', 'simulacao', 266.00, 1, 'uploads/jogos/game_68646e8886d2e.jpg', 'rawereaw\n\n--- REQUISITOS DE SISTEMA ---\nrwerawrewa\n\n--- CARACTERÍSTICAS DE GAMEPLAY ---\nrewarewara\n\n Tags: reawrwae, qwrwaetw4a, taewtawe', '2025-07-01 20:26:00', 'hm', NULL, NULL, 'Disponível');

-- --------------------------------------------------------

--
-- Estrutura para tabela `programafidelidade`
--

CREATE TABLE `programafidelidade` (
  `idFidelidade` int(11) NOT NULL,
  `idCliente` int(11) NOT NULL,
  `moedasAcumuladas` int(11) DEFAULT 0,
  `dataUltimaAtualizacao` datetime DEFAULT current_timestamp(),
  `nivelFidelidade` enum('Bronze','Prata','Ouro','Platina') DEFAULT 'Bronze',
  `totalCompras` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `trocas`
--

CREATE TABLE `trocas` (
  `idTroca` int(11) NOT NULL,
  `idCliente` int(11) NOT NULL,
  `idProdutoUsado` int(11) NOT NULL,
  `idProdutoNovo` int(11) NOT NULL,
  `dataTroca` datetime DEFAULT current_timestamp(),
  `motivoTroca` text DEFAULT NULL,
  `statusTroca` enum('Solicitada','Aprovada','Recusada','Concluída') DEFAULT 'Solicitada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `idVenda` int(11) NOT NULL,
  `idCliente` int(11) NOT NULL,
  `dataVenda` datetime DEFAULT current_timestamp(),
  `valorTotal` decimal(10,2) NOT NULL,
  `statusVenda` enum('Pendente','Pago','Entregue','Cancelado') DEFAULT 'Pendente',
  `metodoPagamento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`idVenda`, `idCliente`, `dataVenda`, `valorTotal`, `statusVenda`, `metodoPagamento`) VALUES
(1, 1, '2025-03-27 20:51:47', 0.00, '', NULL),
(2, 1, '2025-03-27 20:52:14', 0.00, '', NULL),
(3, 1, '2025-03-27 22:06:11', 0.00, '', NULL),
(4, 1, '2025-03-27 22:19:55', 0.00, '', NULL),
(5, 1, '2025-03-27 22:20:20', 0.00, '', NULL),
(6, 1, '2025-03-27 22:27:59', 0.00, '', NULL),
(7, 1, '2025-03-27 22:31:53', 129.00, 'Pendente', NULL),
(8, 1, '2025-03-27 22:49:54', 129.00, '', NULL),
(9, 1, '2025-03-27 22:51:18', 129.00, '', NULL),
(10, 1, '2025-03-27 22:52:42', 129.00, '', NULL),
(11, 1, '2025-03-27 22:56:45', 129.00, '', NULL),
(12, 1, '2025-03-27 23:05:32', 129.00, '', NULL),
(13, 1, '2025-03-27 23:14:20', 129.00, '', NULL),
(14, 1, '2025-03-27 23:25:01', 129.00, '', NULL),
(15, 1, '2025-03-27 23:25:21', 129.00, '', NULL),
(16, 1, '2025-03-27 23:28:17', 129.00, '', NULL),
(17, 1, '2025-03-27 23:28:36', 129.00, '', NULL),
(18, 1, '2025-03-27 23:28:49', 129.00, '', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `idCliente` int(11) NOT NULL,
  `idProduto` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idCliente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_cliente_email` (`email`);

--
-- Índices de tabela `imagensproduto`
--
ALTER TABLE `imagensproduto`
  ADD PRIMARY KEY (`idImagemProduto`),
  ADD KEY `imagensproduto_ibfk_1` (`idProduto`);

--
-- Índices de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`idItemVenda`),
  ADD KEY `idVenda` (`idVenda`),
  ADD KEY `idProduto` (`idProduto`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`idProduto`),
  ADD KEY `idx_produto_categoria` (`categoria`);

--
-- Índices de tabela `programafidelidade`
--
ALTER TABLE `programafidelidade`
  ADD PRIMARY KEY (`idFidelidade`),
  ADD UNIQUE KEY `idCliente` (`idCliente`);

--
-- Índices de tabela `trocas`
--
ALTER TABLE `trocas`
  ADD PRIMARY KEY (`idTroca`),
  ADD KEY `idCliente` (`idCliente`),
  ADD KEY `idProdutoUsado` (`idProdutoUsado`),
  ADD KEY `idProdutoNovo` (`idProdutoNovo`),
  ADD KEY `idx_troca_data` (`dataTroca`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`idVenda`),
  ADD KEY `idCliente` (`idCliente`),
  ADD KEY `idx_venda_data` (`dataVenda`);

--
-- Índices de tabela `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client_product` (`idCliente`,`idProduto`),
  ADD KEY `idx_client` (`idCliente`),
  ADD KEY `idx_product` (`idProduto`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `imagensproduto`
--
ALTER TABLE `imagensproduto`
  MODIFY `idImagemProduto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `idItemVenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `idProduto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `programafidelidade`
--
ALTER TABLE `programafidelidade`
  MODIFY `idFidelidade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `trocas`
--
ALTER TABLE `trocas`
  MODIFY `idTroca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `idVenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `imagensproduto`
--
ALTER TABLE `imagensproduto`
  ADD CONSTRAINT `imagensproduto_ibfk_1` FOREIGN KEY (`idProduto`) REFERENCES `produtos` (`idProduto`) ON DELETE CASCADE;

--
-- Restrições para tabelas `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`idVenda`) REFERENCES `vendas` (`idVenda`),
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`idProduto`) REFERENCES `produtos` (`idProduto`);

--
-- Restrições para tabelas `programafidelidade`
--
ALTER TABLE `programafidelidade`
  ADD CONSTRAINT `programafidelidade_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`);

--
-- Restrições para tabelas `trocas`
--
ALTER TABLE `trocas`
  ADD CONSTRAINT `trocas_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`),
  ADD CONSTRAINT `trocas_ibfk_2` FOREIGN KEY (`idProdutoUsado`) REFERENCES `produtos` (`idProduto`),
  ADD CONSTRAINT `trocas_ibfk_3` FOREIGN KEY (`idProdutoNovo`) REFERENCES `produtos` (`idProduto`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`);

--
-- Restrições para tabelas `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`idProduto`) REFERENCES `produtos` (`idProduto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
