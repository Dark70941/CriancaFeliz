-- =====================================================
-- ALTERAÇÃO: Mudar campos água, esgoto e energia
-- de BOOLEAN para VARCHAR para aceitar strings
-- =====================================================

-- Alterar campo água de BOOLEAN para VARCHAR
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN agua VARCHAR(50) DEFAULT NULL;

-- Alterar campo esgoto de BOOLEAN para VARCHAR  
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN esgoto VARCHAR(50) DEFAULT NULL;

-- Alterar campo energia de BOOLEAN para VARCHAR
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN energia VARCHAR(50) DEFAULT NULL;

-- =====================================================
-- NOTA: Se houver dados existentes com valores 0 ou 1,
-- eles precisarão ser migrados manualmente ou via script
-- =====================================================

