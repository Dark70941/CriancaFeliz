# 🗄️ Banco de Dados - Sistema Criança Feliz

## 📋 Estrutura do Banco

### Tabelas Principais:
1. **Usuario** - Usuários do sistema
2. **Atendido** - Crianças/adolescentes atendidos
3. **Responsavel** - Responsáveis pelos atendidos
4. **Ficha_Acolhimento** - Fichas de acolhimento
5. **Ficha_Socioeconomico** - Fichas socioeconômicas
6. **Familia** - Composição familiar
7. **Despesas** - Despesas e rendas
8. **Encontro** - Registro de encontros/evoluções
9. **Documento** - Documentos anexados
10. **Agenda** - Notificações e lembretes
11. **Log** - Auditoria de alterações

---

## 🚀 Como Instalar

### **Passo 1: Iniciar XAMPP**
```
1. Abra o XAMPP Control Panel
2. Inicie o Apache
3. Inicie o MySQL
```

### **Passo 2: Executar Migração**

#### **Opção A: Via Navegador (Recomendado)**
```
1. Acesse: http://localhost/CriancaFeliz/database/migrate.php
2. Aguarde a mensagem de sucesso
```

#### **Opção B: Via phpMyAdmin**
```
1. Acesse: http://localhost/phpmyadmin
2. Clique em "Importar"
3. Selecione o arquivo: database/migration.sql
4. Clique em "Executar"
```

#### **Opção C: Via Linha de Comando**
```bash
cd c:\xampp\htdocs\CriancaFeliz\database
php migrate.php
```

---

## 🔑 Credenciais Padrão

Após a migração, use estas credenciais para login:

```
Email: admin@criancafeliz.org
Senha: admin123
```

---

## 📊 Diagrama de Relacionamentos

```
Usuario
  └── Log (auditoria)
  └── Encontro

Responsavel
  └── Atendido
      ├── Ficha_Acolhimento
      ├── Ficha_Socioeconomico
      │   ├── Familia
      │   └── Despesas
      ├── Encontro
      └── Documento
```

---

## 🔧 Configuração

Edite o arquivo `app/Config/Database.php` se necessário:

```php
private static $host = 'localhost';
private static $dbname = 'criancafeliz';
private static $username = 'root';
private static $password = '';
```

---

## ✅ Verificar Instalação

Execute este SQL no phpMyAdmin:

```sql
USE criancafeliz;
SHOW TABLES;
```

Você deve ver 11 tabelas:
- Agenda
- Atendido
- Despesas
- Documento
- Encontro
- Familia
- Ficha_Acolhimento
- Ficha_Socioeconomico
- Log
- Responsavel
- Usuario

---

## 🔄 Migração de Dados JSON para MySQL

Após instalar o banco, você pode migrar os dados existentes (JSON) para MySQL.

Um script de migração será criado em breve.

---

## 📝 Triggers Implementados

### Log Automático de Alterações:
- `log_update_all` - Registra atualizações em Usuario
- `log_insert_all` - Registra inserções em Usuario
- `log_delete_all` - Registra exclusões em Usuario

### Como Funciona:
```php
// Definir usuário logado antes de operações
Database::setLoggedUser($_SESSION['user_id']);

// Qualquer INSERT/UPDATE/DELETE em Usuario será logado automaticamente
```

---

## 🛡️ Segurança

### Senhas:
- Armazenadas com hash bcrypt (password_hash)
- Nunca armazenadas em texto puro

### SQL Injection:
- Todas as queries usam prepared statements
- PDO com parâmetros bindados

### Auditoria:
- Todas as alterações em Usuario são logadas
- Rastreabilidade completa

---

## 📈 Performance

### Índices Criados:
- `idx_usuario_email` - Busca rápida por email
- `idx_atendido_cpf` - Busca rápida por CPF
- `idx_responsavel_cpf` - Busca rápida por CPF do responsável
- `idx_log_usuario` - Filtro de logs por usuário
- `idx_log_data` - Filtro de logs por data
- `idx_encontro_atendido` - Busca de encontros por atendido
- `idx_documento_atendido` - Busca de documentos por atendido

---

## 🔄 Backup

### Backup Manual:
```bash
# Via mysqldump
mysqldump -u root criancafeliz > backup_$(date +%Y%m%d).sql

# Via phpMyAdmin
1. Selecione o banco "criancafeliz"
2. Clique em "Exportar"
3. Escolha "SQL"
4. Clique em "Executar"
```

### Restaurar Backup:
```bash
mysql -u root criancafeliz < backup_20250114.sql
```

---

## 🐛 Troubleshooting

### Erro: "Access denied for user 'root'"
```
Solução: Verifique usuário/senha em app/Config/Database.php
```

### Erro: "Unknown database 'criancafeliz'"
```
Solução: Execute a migração novamente
```

### Erro: "Table already exists"
```
Solução: Normal, a migração usa CREATE TABLE IF NOT EXISTS
```

---

## 📞 Suporte

Para problemas, verifique:
1. XAMPP está rodando (Apache + MySQL)
2. Arquivo de configuração está correto
3. Logs de erro em `error_log`

---

**Banco de dados pronto para uso! 🎉**
