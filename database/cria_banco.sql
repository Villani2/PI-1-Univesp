-- Cria banco
CREATE DATABASE IF NOT EXISTS distribuidora DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE distribuidora;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Tabela de doces
CREATE TABLE doces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    descricao TEXT NOT NULL,
    categoria_id INT,
    imagem VARCHAR(255),
    estoque INT DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Inserção de categorias
INSERT INTO categorias (nome) VALUES
('Balas'), ('Chocolates'), ('Pirulitos');
