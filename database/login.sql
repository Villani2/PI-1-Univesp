--Inserção do Usuário para Logar

INSERT INTO usuarios (nome, email, senha)
VALUES ('Administrador', 'admin@doces.com', SHA2('1234', 256));