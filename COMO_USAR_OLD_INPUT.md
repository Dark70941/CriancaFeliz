# 📋 COMO USAR PRESERVAÇÃO DE CAMPOS APÓS ERRO

## 🎯 Objetivo

Quando um formulário retorna com erro (validação, banco de dados, etc.), os campos preenchidos pelo usuário são preservados automaticamente, evitando que ele precise digitar tudo novamente.

---

## ✅ Como funciona

### **1. Sistema Automático**
- Quando há erro, o sistema salva automaticamente todos os campos de `$_POST` na sessão
- Na próxima página, os valores ficam disponíveis através da função `old()`
- Após usar, os valores são limpos automaticamente

### **2. Função `old()`**

```php
old($campo, $valorPadrao = '')
```

**Parâmetros:**
- `$campo`: Nome do campo (name do input)
- `$valorPadrao`: Valor padrão se não houver valor antigo (opcional)

**Retorna:**
- O valor antigo do campo (já com `htmlspecialchars` aplicado)
- Ou o valor padrão se não houver valor antigo

---

## 📝 EXEMPLOS DE USO

### **1. Input Text**

```php
<input type="text" 
       name="nome" 
       value="<?php echo old('nome'); ?>" 
       placeholder="Nome completo">
```

### **2. Input Email**

```php
<input type="email" 
       name="email" 
       value="<?php echo old('email'); ?>" 
       placeholder="email@exemplo.com">
```

### **3. Input com valor padrão**

```php
<input type="text" 
       name="cidade" 
       value="<?php echo old('cidade', 'São Paulo'); ?>">
```

### **4. Textarea**

```php
<textarea name="observacao" 
          placeholder="Observações..."><?php echo old('observacao'); ?></textarea>
```

### **5. Select (Dropdown)**

```php
<select name="tipo_motivo">
    <option value="">Selecione...</option>
    <option value="idade" <?php echo old('tipo_motivo') === 'idade' ? 'selected' : ''; ?>>
        Idade
    </option>
    <option value="outros" <?php echo old('tipo_motivo') === 'outros' ? 'selected' : ''; ?>>
        Outros
    </option>
</select>
```

### **6. Checkbox**

```php
<input type="checkbox" 
       name="pode_retornar" 
       value="1" 
       <?php echo old('pode_retornar', '1') === '1' ? 'checked' : ''; ?>>
```

### **7. Radio Buttons**

```php
<input type="radio" 
       name="sexo" 
       value="M" 
       <?php echo old('sexo') === 'M' ? 'checked' : ''; ?>> Masculino

<input type="radio" 
       name="sexo" 
       value="F" 
       <?php echo old('sexo') === 'F' ? 'checked' : ''; ?>> Feminino
```

### **8. Input Date**

```php
<input type="date" 
       name="data_nascimento" 
       value="<?php echo old('data_nascimento'); ?>">
```

---

## 🔧 COMO IMPLEMENTAR EM NOVOS FORMULÁRIOS

### **Passo 1: Não precisa fazer nada no Controller!**
O sistema já preserva automaticamente quando você usa:
```php
$this->redirectWithError('pagina.php', 'Mensagem de erro');
```

### **Passo 2: Usar `old()` nos campos da View**

**ANTES (sem preservação):**
```php
<input type="text" name="nome" placeholder="Nome">
```

**DEPOIS (com preservação):**
```php
<input type="text" name="nome" value="<?php echo old('nome'); ?>" placeholder="Nome">
```

---

## 📋 FORMULÁRIOS IMPORTANTES PARA ATUALIZAR

### **Alta Prioridade:**
- ✅ `app/Views/desligamento/novo.php` - JÁ IMPLEMENTADO
- 🔲 `app/Views/acolhimento/create.php` - Cadastro de atendidos
- 🔲 `app/Views/users/create.php` - Cadastro de usuários
- 🔲 `app/Views/users/edit.php` - Edição de usuários

### **Média Prioridade:**
- 🔲 `app/Views/socioeconomico/create.php` - Formulário socioeconômico
- 🔲 `app/Views/faltas/gerenciar_oficinas.php` - Cadastro de oficinas

### **Baixa Prioridade:**
- 🔲 Outros formulários menores

---

## 🎯 EXEMPLO COMPLETO

### **Formulário:**
```php
<form method="POST" action="cadastro.php">
    <!-- Nome -->
    <div class="form-group">
        <label>Nome *</label>
        <input type="text" name="nome" value="<?php echo old('nome'); ?>" required>
    </div>
    
    <!-- Email -->
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="<?php echo old('email'); ?>" required>
    </div>
    
    <!-- Tipo -->
    <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
            <option value="">Selecione...</option>
            <option value="1" <?php echo old('tipo') === '1' ? 'selected' : ''; ?>>Tipo 1</option>
            <option value="2" <?php echo old('tipo') === '2' ? 'selected' : ''; ?>>Tipo 2</option>
        </select>
    </div>
    
    <!-- Observações -->
    <div class="form-group">
        <label>Observações</label>
        <textarea name="obs"><?php echo old('obs'); ?></textarea>
    </div>
    
    <!-- Ativo -->
    <div class="form-group">
        <input type="checkbox" name="ativo" value="1" 
               <?php echo old('ativo', '1') === '1' ? 'checked' : ''; ?>>
        <label>Ativo</label>
    </div>
    
    <button type="submit">Salvar</button>
</form>
```

### **Controller (já funciona automaticamente):**
```php
public function salvar() {
    try {
        // Sua lógica de salvamento...
        
        if ($erro) {
            // Campos serão preservados automaticamente!
            $this->redirectWithError('formulario.php', 'Erro ao salvar!');
        }
        
        // Sucesso
        $this->redirectWithSuccess('lista.php', 'Salvo com sucesso!');
        
    } catch (Exception $e) {
        // Campos serão preservados automaticamente!
        $this->redirectWithError('formulario.php', $e->getMessage());
    }
}
```

---

## ✅ BENEFÍCIOS

1. ✅ **Melhor experiência do usuário** - Não precisa digitar tudo de novo
2. ✅ **Automático** - Funciona em qualquer formulário que use `redirectWithError()`
3. ✅ **Seguro** - Valores são sanitizados com `htmlspecialchars()`
4. ✅ **Limpo** - Valores são removidos automaticamente após uso
5. ✅ **Simples** - Só precisa adicionar `old()` nos campos

---

## 🎉 PRONTO!

Agora todos os formulários podem preservar valores após erros!

**Próximo passo:** Implementar em todos os formulários importantes do sistema.
