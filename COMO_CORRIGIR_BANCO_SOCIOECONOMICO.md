# Como Corrigir o Banco de Dados - Ficha Socioeconômica

## ⚠️ IMPORTANTE: Você PRECISA executar o script SQL no banco!

### O que precisa ser alterado:

1. **Campos `agua`, `esgoto` e `energia`** precisam ser alterados de **BOOLEAN/TINYINT** para **VARCHAR(50)** no banco.

### Como fazer:

1. Abra o **phpMyAdmin** (http://localhost/phpmyadmin)
2. Selecione o banco **`criancafeliz`**
3. Vá na aba **SQL**
4. Execute este script:

```sql
-- Alterar campo água
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN agua VARCHAR(50) DEFAULT NULL;

-- Alterar campo esgoto  
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN esgoto VARCHAR(50) DEFAULT NULL;

-- Alterar campo energia
ALTER TABLE Ficha_Socioeconomico 
MODIFY COLUMN energia VARCHAR(50) DEFAULT NULL;
```

OU execute o arquivo completo: `database/fix_socioeconomico_tables.sql`

### Verificar se funcionou:

Depois de executar, verifique se os campos foram alterados:
- Vá em **Estrutura** da tabela `Ficha_Socioeconomico`
- Os campos `agua`, `esgoto` e `energia` devem aparecer como **VARCHAR(50)**

### Se ainda não salvar:

Verifique os logs de erro do PHP em:
- `C:\xampp\php\logs\php_error_log`
- Ou veja o erro diretamente na tela (se `display_errors` estiver ativo)

