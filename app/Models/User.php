<?php

/**
 * Model para usuários do sistema
 */
class User extends BaseModel {
    
    public function __construct() {
        parent::__construct('users.json');
        $this->createDefaultUser();
    }
    
    /**
     * Cria usuário padrão se não existir
     */
    private function createDefaultUser() {
        if (empty($this->data)) {
            $this->create([
                'name' => 'Administrador',
                'email' => 'admin@criancafeliz.org',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active'
            ]);
        }
    }
    
    /**
     * Autentica usuário
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remover senha dos dados retornados
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Busca usuário por email
     */
    public function findByEmail($email) {
        foreach ($this->data as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null) {
        foreach ($this->data as $user) {
            if ($user['email'] === $email && $user['id'] !== $excludeId) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Cria novo usuário com validação
     */
    public function createUser($data) {
        // Validações
        if (empty($data['name'])) {
            throw new Exception('Nome é obrigatório');
        }
        
        if (empty($data['email']) || !validateEmail($data['email'])) {
            throw new Exception('Email válido é obrigatório');
        }
        
        if ($this->emailExists($data['email'])) {
            throw new Exception('Email já está em uso');
        }
        
        if (empty($data['password']) || !validatePassword($data['password'])) {
            throw new Exception('Senha deve ter pelo menos 6 caracteres');
        }
        
        // Hash da senha
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role'] = $data['role'] ?? 'user';
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->create($data);
    }
    
    /**
     * Atualiza usuário com validação
     */
    public function updateUser($id, $data) {
        $user = $this->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Validações
        if (isset($data['email'])) {
            if (!validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            
            if ($this->emailExists($data['email'], $id)) {
                throw new Exception('Email já está em uso');
            }
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            if (!validatePassword($data['password'])) {
                throw new Exception('Senha deve ter pelo menos 6 caracteres');
            }
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Manter senha atual se não foi fornecida nova
            $data['password'] = $user['password'];
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Lista usuários sem senhas
     */
    public function findAllSafe() {
        $users = $this->findAll();
        foreach ($users as &$user) {
            unset($user['password']);
        }
        return $users;
    }
}
