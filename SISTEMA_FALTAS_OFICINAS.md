# 📋 SISTEMA DE FALTAS E DESLIGAMENTO - COMPLETO

## 🎯 Visão Geral

Sistema completo de controle de faltas e desligamentos com suporte a:
- ✅ **Faltas por Dia** - Controle geral diário
- ✅ **Faltas por Oficina** - Controle específico por atividade
- ✅ **Alertas Automáticos** - Identificação de atendidos com excesso de faltas
- ✅ **Desligamento Manual e Automático** - Gestão de desligamentos
- ✅ **Interface Limpa** - Telas separadas por função
- ✅ **Checkbox Intuitivo** - Marcar/desmarcar presença facilmente

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas

1. **Oficina** - Cadastro de oficinas/atividades
2. **Frequencia_Dia** - Registro de presença/falta por dia
3. **Frequencia_Oficina** - Registro de presença/falta por oficina
4. **Desligamento** - Registro de desligamentos

### Views Criadas

1. **Estatisticas_Frequencia** - Estatísticas automáticas por atendido
2. **Atendidos_Com_Alerta** - Atendidos com 2+ faltas não justificadas

---

## 🚀 Como Usar

### 1️⃣ Instalação do Banco de Dados

**Passo 1:** Abra o phpMyAdmin (http://localhost/phpmyadmin)

**Passo 2:** Selecione o banco `criancafeliz`

**Passo 3:** Vá na aba **SQL** e execute o script:
```
database/migration_faltas_oficinas.sql
```

**Passo 4:** Verifique se as tabelas foram criadas:
- Oficina
- Frequencia_Dia
- Frequencia_Oficina
- Desligamento

---

### 2️⃣ Navegação no Sistema

#### **Menu Lateral - Novos Ícones**

| Ícone | Função | Descrição |
|-------|--------|-----------|
| 📅 | **Faltas - Por Dia** | Lançamento de faltas diário |
| 👨‍🏫 | **Faltas - Por Oficina** | Lançamento por oficina específica |
| ⚠️ | **Alertas de Faltas** | Atendidos com excesso de faltas |
| 👤❌ | **Desligamentos** | Gerenciar desligamentos |
| ⚙️ | **Gerenciar Oficinas** | Cadastrar/editar oficinas (Admin) |

---

## 📖 Funcionalidades Detalhadas

### 🔹 Controle de Faltas - Por Dia

**Acesso:** Menu Lateral > Ícone de Calendário

**Funcionalidades:**
- ✅ Filtrar por data
- ✅ Buscar atendido por nome/CPF
- ✅ Marcar presença/falta com checkbox/radio
- ✅ Adicionar justificativa para faltas
- ✅ Ver histórico completo

**Como Usar:**
1. Selecione a **data** desejada
2. Para cada atendido, marque:
   - **✓ Presente** - Atendido compareceu
   - **✗ Falta** - Atendido faltou
   - **J Justificada** - Falta com justificativa (abre campo de texto)
3. A marcação é **salva automaticamente** via AJAX
4. Clique no ícone de **histórico** para ver detalhes do atendido

---

### 🔹 Controle de Faltas - Por Oficina

**Acesso:** Menu Lateral > Ícone de Professor

**Funcionalidades:**
- ✅ Selecionar oficina específica
- ✅ Filtrar por data
- ✅ Marcar presença/falta individual
- ✅ Controle separado por atividade

**Como Usar:**
1. Selecione a **oficina** no dropdown
2. Selecione a **data**
3. Clique em **Carregar**
4. Marque presença/falta para cada atendido
5. Sistema salva automaticamente

**Exemplo de Uso:**
- Oficina: "Reforço Escolar"
- Data: 27/10/2025
- Marcar presenças dos alunos que compareceram

---

### 🔹 Alertas de Faltas

**Acesso:** Menu Lateral > Ícone de Alerta

**Funcionalidades:**
- ✅ Lista atendidos com 2+ faltas não justificadas
- ✅ Diferencia alertas: **ALERTA** (2 faltas) e **CRÍTICO** (3+ faltas)
- ✅ Mostra total de faltas e última falta
- ✅ Botão para desligar direto (se crítico)

**Status de Alerta:**
- 🟡 **ALERTA** - 2 faltas não justificadas
- 🔴 **CRÍTICO** - 3 ou mais faltas (pode desligar)

**Ações Disponíveis:**
- Ver histórico completo
- Desligar atendido (se crítico)

---

### 🔹 Sistema de Desligamento

**Acesso:** Menu Lateral > Ícone de Usuário com X

**Funcionalidades:**
- ✅ Listar todos os desligamentos
- ✅ Filtrar por tipo/data
- ✅ Desligar manualmente
- ✅ Desligamento automático por faltas
- ✅ Reativar atendidos
- ✅ Estatísticas de desligamentos

**Tipos de Motivo:**
1. **Idade** - Atendido ultrapassou idade limite
2. **Excesso de Faltas** - 3+ faltas não justificadas
3. **Pedido da Família** - Família solicitou
4. **Transferência** - Mudou para outra instituição
5. **Outros** - Outros motivos

**Como Desligar Manualmente:**
1. Acesse **Desligamentos**
2. Vá em **Alertas** ou busque o atendido
3. Clique em **Desligar**
4. Preencha:
   - Tipo de motivo
   - Descrição detalhada
   - Observações (opcional)
   - Marque se pode retornar
5. Confirme o desligamento

**Desligamento Automático:**
1. Acesse **Desligamentos**
2. Clique em **Processar Automático**
3. Sistema busca atendidos com 3+ faltas
4. Desliga automaticamente
5. Mostra quantos foram desligados

**Reativar Atendido:**
1. Localize o desligamento na lista
2. Clique em **Reativar**
3. Confirme a ação
4. Atendido volta ao status **Ativo**

---

### 🔹 Gerenciar Oficinas (Admin)

**Acesso:** Menu Lateral > Ícone de Engrenagens (apenas Admin)

**Funcionalidades:**
- ✅ Cadastrar novas oficinas
- ✅ Editar oficinas existentes
- ✅ Ativar/desativar oficinas
- ✅ Configurar horários e dias

**Como Cadastrar Oficina:**
1. Clique em **Nova Oficina**
2. Preencha:
   - Nome da oficina
   - Descrição (opcional)
   - Dia da semana
   - Horário início/fim
3. Salve

**Oficinas Padrão Criadas:**
- Reforço Escolar (Segunda, 14:00-16:00)
- Artes (Terça, 14:00-16:00)
- Esportes (Quarta, 14:00-16:00)
- Música (Quinta, 14:00-16:00)
- Dança (Sexta, 14:00-16:00)
- Teatro (Sábado, 09:00-11:00)

---

### 🔹 Histórico do Atendido

**Acesso:** Qualquer tela de faltas > Ícone de Histórico

**Funcionalidades:**
- ✅ Estatísticas gerais (dia e oficina)
- ✅ Timeline de todos os registros
- ✅ Detalhes de justificativas
- ✅ Quem registrou cada falta/presença
- ✅ Percentual de presença

**Informações Exibidas:**
- Total de presenças (dia e oficina)
- Total de faltas (dia e oficina)
- Total de justificadas (dia e oficina)
- Percentual de presença
- Timeline cronológica completa

---

## 🎨 Interface do Sistema

### Design Limpo
- ✅ Telas separadas por função
- ✅ Cards coloridos e intuitivos
- ✅ Checkboxes/radios grandes e fáceis de clicar
- ✅ Cores indicativas (verde=presente, vermelho=falta, amarelo=justificada)
- ✅ Responsivo (funciona em mobile)

### Feedback Visual
- ✅ Toast notifications ao salvar
- ✅ Badges de status coloridos
- ✅ Ícones intuitivos
- ✅ Loading states
- ✅ Confirmações de ação

---

## 📊 Relatórios e Estatísticas

### Estatísticas Automáticas

**Por Atendido:**
- Total de presenças
- Total de faltas
- Total de justificadas
- Percentual de presença
- Última atividade

**Gerais:**
- Total de desligamentos
- Desligamentos por tipo
- Desligamentos automáticos
- Atendidos em alerta

---

## 🔒 Permissões

### Todos os Usuários
- ✅ Ver faltas por dia
- ✅ Ver faltas por oficina
- ✅ Marcar presença/falta
- ✅ Ver alertas
- ✅ Ver histórico

### Apenas Admin
- ✅ Gerenciar oficinas
- ✅ Desligar atendidos
- ✅ Processar desligamentos automáticos
- ✅ Reativar atendidos
- ✅ Ver relatórios completos

---

## ⚙️ Configurações

### Status de Presença

| Código | Descrição | Cor |
|--------|-----------|-----|
| **P** | Presente | Verde |
| **F** | Falta | Vermelho |
| **J** | Justificada | Amarelo |

### Regras de Negócio

1. **Alerta em 2 faltas** - Sistema gera alerta
2. **Crítico em 3 faltas** - Pode ser desligado
3. **Desligamento automático** - Se configurado, desliga em 3+ faltas
4. **Reativação permitida** - Se configurado ao desligar
5. **Histórico mantido** - Todos os registros são preservados

---

## 🚨 Dicas Importantes

### ✅ Boas Práticas

1. **Sempre justifique faltas quando possível**
   - Evita desligamentos indevidos
   - Mantém histórico completo

2. **Verifique alertas regularmente**
   - Previne desligamentos automáticos
   - Permite ação proativa

3. **Use controle por oficina para atividades específicas**
   - Mais preciso que controle por dia
   - Permite acompanhamento por atividade

4. **Revise desligamentos antes de processar automaticamente**
   - Pode haver justificativas pendentes
   - Evita desligamentos injustos

### ⚠️ Avisos

- ❌ **Não remova registros de frequência** - Mantém histórico
- ❌ **Desligamento é reversível** - Mas deve ser usado com cuidado
- ❌ **Backup regular** - Dados importantes
- ✅ **Treine a equipe** - Sistema intuitivo mas treinamento ajuda

---

## 🔧 Manutenção

### Backup

**Frequência recomendada:** Semanal

**Tabelas importantes:**
- Frequencia_Dia
- Frequencia_Oficina
- Desligamento
- Oficina

### Limpeza

**Recomendações:**
- Manter histórico de pelo menos 1 ano
- Arquivar desligamentos antigos
- Não deletar registros, apenas arquivar

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte esta documentação
2. Verifique logs do sistema
3. Entre em contato com o administrador

---

## 🎉 Conclusão

O novo sistema de faltas e desligamento está **pronto para uso**!

### Principais Benefícios:
✅ Interface limpa e organizada
✅ Controle por dia E por oficina
✅ Alertas automáticos
✅ Desligamento inteligente
✅ Histórico completo
✅ Relatórios detalhados
✅ Totalmente integrado ao sistema

**Desenvolvido com excelência técnica! 🚀**
