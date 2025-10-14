CREATE DATABASE IF NOT EXISTS criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE criancafeliz;

CREATE TABLE Usuario (
  idusuario INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  email VARCHAR(100),
  Senha VARCHAR(100),
  nivel VARCHAR(50),
  status VARCHAR(20)
);

CREATE TABLE Agenda (
  id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
  mensagem VARCHAR(255),
  tipo VARCHAR(50),
  lida BOOLEAN,
  data_envio DATETIME
);


CREATE TABLE Log (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  data_alteracao DATETIME,
  registro_alt VARCHAR(100),
  valor_anterior VARCHAR(100),
  valor_atual VARCHAR(100),
  acao VARCHAR(50),
  tabela_afetada VARCHAR(100),
  id_usuario INT,
  FOREIGN KEY (id_usuario) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


CREATE TABLE Responsavel (
  idresponsavel INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  cpf VARCHAR(14),
  telefone VARCHAR(15),
  email VARCHAR(100),
  parentesco VARCHAR(50)
);


CREATE TABLE Atendido (
  idatendido INT AUTO_INCREMENT PRIMARY KEY,
  status VARCHAR(20),
  data_cadastro DATE,
  nome VARCHAR(100),
  data_nascimento DATE,
  faixa_etaria VARCHAR(20),
  cpf VARCHAR(14),
  rg VARCHAR(20),
  endereco VARCHAR(255),
  foto VARCHAR(255),
  id_responsavel INT,
  FOREIGN KEY (id_responsavel) REFERENCES Responsavel(idresponsavel)
    ON UPDATE CASCADE
    ON DELETE SET NULL
);

CREATE TABLE Encontro (
  id_encontro INT AUTO_INCREMENT PRIMARY KEY,
  Dataencontro DATE,
  ID_usuario INT,
  evolucao VARCHAR(255),
  id_atendido INT,
  FOREIGN KEY (ID_usuario) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


CREATE TABLE Documento (
  iddocumento INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(50),
  arquivo VARCHAR(255),
  data_upload DATETIME,
  IDatendido INT,
  FOREIGN KEY (IDatendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


CREATE TABLE Ficha_Socioeconomico (
  idficha INT AUTO_INCREMENT PRIMARY KEY,
  agua BOOLEAN,
  esgoto BOOLEAN,
  renda_familiar DECIMAL(10,2),
  energia BOOLEAN,
  qtd_pessoas INT,
  cond_residencia VARCHAR(100),
  moradia VARCHAR(100),
  nr_veiculos INT,
  observacoes VARCHAR(255),
  entrevistado VARCHAR(100),
  residencia VARCHAR(100),
  nr_comodos INT,
  construcao VARCHAR(100),
  id_atendido INT UNIQUE,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


CREATE TABLE Familia (
  id_familia INT AUTO_INCREMENT PRIMARY KEY,
  id_ficha INT,
  nome VARCHAR(100),
  parentesco VARCHAR(50),
  data_nasc DATE,
  formacao VARCHAR(100),
  renda DECIMAL(10,2),
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


CREATE TABLE Despesas (
  id_despesa INT AUTO_INCREMENT PRIMARY KEY,
  valor_despesa DECIMAL(10,2),
  tipo_renda VARCHAR(50),
  valor_renda DECIMAL(10,2),
  id_ficha INT,
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);


-- Variável de sessão para armazenar o usuário logado (defina no login)
-- SET @usuario_id = 1;  -- exemplo de definição antes das operações

DELIMITER $$

CREATE TRIGGER log_update_all
AFTER UPDATE ON Usuario
FOR EACH ROW
BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Campo alterado em Usuario.idusuario = ', OLD.idusuario),
        CONCAT('Nome antigo: ', OLD.nome, ', Email antigo: ', OLD.email),
        CONCAT('Nome novo: ', NEW.nome, ', Email novo: ', NEW.email),
        'UPDATE',
        'Usuario',
        @usuario_id
    );
END$$

CREATE TRIGGER log_insert_all
AFTER INSERT ON Usuario
FOR EACH ROW
BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Novo registro em Usuario.idusuario = ', NEW.idusuario),
        NULL,
        CONCAT('Nome: ', NEW.nome, ', Email: ', NEW.email),
        'INSERT',
        'Usuario',
        @usuario_id
    );
END$$

CREATE TRIGGER log_delete_all
AFTER DELETE ON Usuario
FOR EACH ROW
BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Removido Usuario.idusuario = ', OLD.idusuario),
        CONCAT('Nome: ', OLD.nome, ', Email: ', OLD.email),
        NULL,
        'DELETE',
        'Usuario',
        @usuario_id
    );
END$$

DELIMITER ;
