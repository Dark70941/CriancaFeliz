-- =====================================================
-- SCRIPT DE CORREÇÃO E ATUALIZAÇÃO
-- Tabelas Ficha_Socioeconomico, Familia e Despesas
-- =====================================================

-- Verificar e alterar nomes de tabelas para minúsculas se necessário (MySQL case-sensitive)
-- Nota: MySQL geralmente trata nomes de tabelas como case-insensitive no Windows

-- =====================================================
-- 1. Criar tabela Ficha_Socioeconomico se não existir
-- =====================================================
CREATE TABLE IF NOT EXISTS Ficha_Socioeconomico (
  idficha INT AUTO_INCREMENT PRIMARY KEY,
  agua VARCHAR(50) DEFAULT NULL,
  esgoto VARCHAR(50) DEFAULT NULL,
  renda_familiar DECIMAL(10,2) DEFAULT NULL,
  energia VARCHAR(50) DEFAULT NULL,
  qtd_pessoas INT DEFAULT NULL,
  cond_residencia VARCHAR(100) DEFAULT NULL,
  moradia VARCHAR(100) DEFAULT NULL,
  nr_veiculos INT DEFAULT NULL,
  observacoes TEXT DEFAULT NULL,
  entrevistado VARCHAR(100) DEFAULT NULL,
  residencia VARCHAR(100) DEFAULT NULL,
  nr_comodos INT DEFAULT NULL,
  construcao VARCHAR(100) DEFAULT NULL,
  id_atendido INT UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2. ALTERAR campos água, esgoto e energia de BOOLEAN/TINYINT para VARCHAR
-- =====================================================

-- Alterar campo água (se existir como BOOLEAN/TINYINT)
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN agua VARCHAR(50) DEFAULT NULL;

-- Alterar campo esgoto (se existir como BOOLEAN/TINYINT)
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN esgoto VARCHAR(50) DEFAULT NULL;

-- Alterar campo energia (se existir como BOOLEAN/TINYINT)
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN energia VARCHAR(50) DEFAULT NULL;

-- =====================================================
-- 3. Criar tabela Familia se não existir
-- =====================================================
CREATE TABLE IF NOT EXISTS Familia (
  id_familia INT AUTO_INCREMENT PRIMARY KEY,
  id_ficha INT NOT NULL,
  nome VARCHAR(100) DEFAULT NULL,
  parentesco VARCHAR(50) DEFAULT NULL,
  data_nasc DATE DEFAULT NULL,
  formacao VARCHAR(100) DEFAULT NULL,
  renda DECIMAL(10,2) DEFAULT NULL,
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 4. Criar tabela Despesas se não existir
-- =====================================================
CREATE TABLE IF NOT EXISTS Despesas (
  id_despesa INT AUTO_INCREMENT PRIMARY KEY,
  id_ficha INT NOT NULL,
  valor_despesa DECIMAL(10,2) DEFAULT NULL,
  tipo_renda VARCHAR(50) DEFAULT NULL,
  valor_renda DECIMAL(10,2) DEFAULT NULL,
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5. Adicionar índices para melhorar performance
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_ficha_atendido ON Ficha_Socioeconomico(id_atendido);
CREATE INDEX IF NOT EXISTS idx_familia_ficha ON Familia(id_ficha);
CREATE INDEX IF NOT EXISTS idx_despesas_ficha ON Despesas(id_ficha);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

