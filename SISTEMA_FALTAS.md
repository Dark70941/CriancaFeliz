# 📊 Sistema de Controle de Faltas - Criança Feliz

## 🎯 Visão Geral

Sistema completo para gerenciamento de presenças, faltas e desligamentos de atendidos, com alertas automáticos e critérios de desligamento configuráveis.

---

## ✨ Funcionalidades Implementadas

### 1. **Controle de Presenças e Faltas**
- ✅ Registro de presenças em atividades/oficinas/atendimentos
- ✅ Registro de faltas com opção de justificativa
- ✅ Histórico completo de frequência por atendido
- ✅ Estatísticas detalhadas (total de presenças, faltas justificadas/não justificadas, taxa de presença)
- ✅ Busca e filtros por nome/CPF

### 2. **Sistema de Alertas Inteligentes**
- ⚠️ **Alerta de Excesso de Faltas**: Quando atendido atinge 5+ faltas não justificadas
- ⚡ **Alerta de Atenção**: Quando atendido atinge 3-4 faltas não justificadas
- 🎂 **Alerta de Idade Limite**: Quando atendido completa 18 anos (desligamento automático pendente)
- 📅 **Alerta de Proximidade**: Quando atendido está próximo aos 18 anos (17 anos)

### 3. **Desligamentos**
- 🔴 **Desligamento Automático por Idade**: Atendidos que completam 18 anos
- 🔴 **Desligamento Manual**: Por excesso de faltas, mudança de cidade, transferência, etc.
- 📝 **Registro Permanente**: Histórico completo com motivo e observações
- 🔄 **Reativação**: Possibilidade de cancelar desligamento (apenas admin)

### 4. **Integração com Dashboard**
- 📊 Alertas prioritários exibidos no dashboard principal
- 🔗 Links diretos para página de alertas
- 📈 Estatísticas em tempo real

---

## 🗂️ Estrutura de Arquivos

```
CriancaFeliz/
├── app/
│   ├── Models/
│   │   ├── Attendance.php          # Gerencia registros de presença/falta
│   │   └── Desligamento.php        # Gerencia desligamentos
│   ├── Services/
│   │   └── AttendanceService.php   # Lógica de negócio e alertas
│   ├── Controllers/
│   │   └── AttendanceController.php # Endpoints e roteamento
│   └── Views/
│       └── attendance/
│           ├── index.php           # Lista de atendidos com estatísticas
│           ├── show.php            # Detalhes e histórico individual
│           ├── alertas.php         # Página de alertas
│           └── desligamento.php    # Formulário de desligamento
├── data/
│   ├── attendance.json             # Registros de presença/falta
│   ├── desligamentos.json          # Histórico de desligamentos
│   └── attendance_log.json         # Log de ações
└── attendance.php                   # Endpoint principal
```

---

## 🔧 Configurações

### Critérios de Alerta (em `AttendanceService.php`)

```php
const MAX_FALTAS_NAO_JUSTIFICADAS = 5;  // Limite para alerta crítico
const IDADE_DESLIGAMENTO_AUTOMATICO = 18; // Idade para desligamento automático
```

### Tipos de Atividades
- Atendimento
- Oficina
- Reunião
- Evento
- Outro

### Motivos de Desligamento
- **Automático**: Idade (18 anos)
- **Manual**:
  - Excesso de faltas não justificadas
  - Mudança de cidade
  - Transferência para outro programa
  - Solicitação da família
  - Questões comportamentais
  - Outro motivo (com justificativa)

---

## 📋 Como Usar

### 1. **Acessar o Sistema**
- Menu lateral: Clique no ícone 📊 "Controle de Faltas"
- URL direta: `attendance.php`

### 2. **Registrar Presença/Falta**
1. Na lista, clique no ícone 👁️ para ver detalhes do atendido
2. Clique em "Registrar Presença" (verde) ou "Registrar Falta" (laranja)
3. Preencha:
   - Data do registro
   - Tipo de atividade
   - Justificativa (apenas para faltas)
   - Observações (opcional)
4. Confirme o registro

### 3. **Adicionar Justificativa Posterior**
1. No histórico do atendido, localize a falta não justificada
2. Clique em "Adicionar Justificativa"
3. Digite a justificativa e salve

### 4. **Visualizar Alertas**
- Dashboard: Veja alertas prioritários na seção "Alertas Prioritários"
- Página de Alertas: Clique em "Ver Alertas" ou acesse `attendance.php?action=alertas`

### 5. **Processar Desligamento**

#### Desligamento Manual:
1. Acesse os detalhes do atendido
2. Clique em "Desligar Atendido" (apenas admin)
3. Selecione o motivo
4. Adicione observações detalhadas
5. Confirme o desligamento

#### Desligamento Automático por Idade:
1. Acesse a página de alertas
2. Clique em "Processar Desligamentos Automáticos"
3. Sistema desligará automaticamente todos os atendidos com 18+ anos
4. Ou desligar individualmente clicando em "Desligar" no alerta específico

### 6. **Reativar Atendido**
1. Acesse os detalhes do atendido desligado
2. Clique em "Reativar Atendido" (apenas admin)
3. Confirme a reativação

---

## 🎨 Interface

### Cores e Indicadores
- 🟢 **Verde (#27ae60)**: Presenças
- 🔵 **Azul (#3498db)**: Faltas justificadas
- 🟠 **Laranja (#f39c12)**: Faltas não justificadas (1-4)
- 🔴 **Vermelho (#e74c3c)**: Excesso de faltas (5+) ou desligado

### Badges e Status
- **DESLIGADO**: Badge vermelho para atendidos desligados
- **⚠️ ATENÇÃO**: Badge laranja para atendidos com alertas
- **JUSTIFICADA**: Badge azul para faltas justificadas

---

## 🔐 Permissões

### Todos os Usuários
- ✅ Visualizar lista de atendidos
- ✅ Ver detalhes e histórico
- ✅ Registrar presenças
- ✅ Registrar faltas
- ✅ Adicionar justificativas

### Apenas Admin
- ✅ Remover registros
- ✅ Desligar atendidos
- ✅ Reativar atendidos
- ✅ Processar desligamentos automáticos

---

## 📊 Estatísticas e Relatórios

### Por Atendido
- Total de presenças
- Faltas justificadas
- Faltas não justificadas
- Total de faltas
- Taxa de presença (%)
- Última atividade registrada

### Alertas em Tempo Real
- Atendidos com excesso de faltas
- Atendidos com idade limite
- Atendidos próximos aos 18 anos

---

## 🔄 Fluxo de Desligamento Automático

```
1. Atendido completa 18 anos
   ↓
2. Sistema detecta na verificação de alertas
   ↓
3. Alerta crítico é exibido no dashboard
   ↓
4. Admin acessa página de alertas
   ↓
5. Admin clica em "Processar Desligamentos Automáticos"
   ↓
6. Sistema desliga automaticamente todos com 18+ anos
   ↓
7. Registro permanente é criado em desligamentos.json
   ↓
8. Atendido não pode mais receber novos registros
```

---

## 🛡️ Segurança

- ✅ Proteção CSRF em todos os formulários
- ✅ Validação server-side de todos os dados
- ✅ Sanitização automática de inputs
- ✅ Controle de permissões por role
- ✅ Log de todas as ações (attendance_log.json)
- ✅ Confirmação obrigatória para ações críticas

---

## 📝 Estrutura de Dados

### Registro de Presença/Falta (attendance.json)
```json
{
  "id": "presence_xxxxx",
  "atendido_id": "xxxxx",
  "tipo": "presenca|falta",
  "data": "2025-10-09",
  "atividade": "Atendimento",
  "justificada": true|false,
  "justificativa": "Texto da justificativa",
  "observacao": "Observações adicionais",
  "registrado_por": "user_id",
  "registrado_em": "2025-10-09 21:00:00"
}
```

### Registro de Desligamento (desligamentos.json)
```json
{
  "id": "deslig_xxxxx",
  "atendido_id": "xxxxx",
  "atendido_nome": "Nome Completo",
  "atendido_cpf": "123.456.789-00",
  "motivo": "idade|excesso_faltas|outro",
  "tipo_motivo": "manual|idade|excesso_faltas",
  "data_desligamento": "2025-10-09",
  "observacao": "Detalhes do desligamento",
  "automatico": true|false,
  "registrado_por": "user_id",
  "registrado_por_nome": "Nome do Usuário",
  "registrado_em": "2025-10-09 21:00:00"
}
```

---

## 🚀 Próximas Melhorias Sugeridas

1. **Relatórios Avançados**
   - Exportação de relatórios em PDF
   - Gráficos de frequência por período
   - Comparativo entre atendidos

2. **Notificações**
   - Email/SMS para responsáveis quando falta é registrada
   - Notificação automática ao atingir 3 faltas

3. **Integração**
   - Sincronização com calendário de atividades
   - API para apps mobile

4. **Análises**
   - Dashboard de analytics
   - Previsão de desligamentos
   - Identificação de padrões

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `data/attendance_log.json`
2. Consulte este documento
3. Entre em contato com a equipe de desenvolvimento

---

**✅ Sistema implementado e pronto para uso!**

*Última atualização: 09/10/2025*
