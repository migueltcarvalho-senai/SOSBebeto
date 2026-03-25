-- Script de Criação do Banco de Dados SOSBebeto
-- Desenvolvido para compatibilidade com InfinityFree e sistemas Linux Case-Sensitive

-- 1. Cria o banco de dados caso não exista (No InfinityFree, o banco já estará criado com prefixo no vPanel, então você pode pular esta linha e focar apenas no USE e CREATE TABLE)
CREATE DATABASE IF NOT EXISTS sosbebetodb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Seleciona o banco de dados (Substitua 'sosbebetodb' pelo nome gerado no InfinityFree, ex: epiz_12345678_sosbebetodb)
USE sosbebetodb;

-- 3. Cria a tabela principal da fila
CREATE TABLE IF NOT EXISTS ajuda (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_aluno VARCHAR(255),
    descricao VARCHAR(255),
    tipo VARCHAR(255),
    hora DATETIME,
    status_ajuda ENUM('Em andamento', 'em atendimento', 'encerramento'),
    email_google VARCHAR(255)
);
