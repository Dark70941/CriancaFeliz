<?php

/**
 * Model para usuários do sistema - MYSQL
 */
class User extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Usuario', 'idusuario');
        $this->createDefaultUser();
    }
    
    /**
     * Cria usuário padrão se não existir
     */
    private function createDefaultUser() {
        try {
            $count = $this->count();
            if ($count == 0) {
                $this->query(
                    "INSERT INTO Usuario (nome, email, Senha, nivel, status) VALUES (?, ?, ?, ?, ?)",
                    ['Administrador', 'admin@criancafeliz.org', password_hash('admin123', PASSWORD_DEFAULT), 'Administrador', 'Ativo']
                );
            }
        } catch (Exception $e) {
            // Usuário já existe ou erro - ignorar
        }
    }
    
    /**
     * Autentica usuário
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        // Verificar se usuário existe e senha está correta
        if ($user && password_verify($password, $user['Senha'])) {
            // Verificar se usuário está ativo
            $status = strtolower($user['status'] ?? 'inativo');
            if ($status !== 'ativo' && $status !== 'active') {
                return null; // Usuário inativo
            }
            
            // Remover senha dos dados retornados
            unset($user['Senha']);
            // Mapear campos do banco para formato esperado
            $user['id'] = $user['idusuario'];
            $user['name'] = $user['nome'];
            $user['role'] = $user['nivel'];
            return $user;
        }
        
        return null;
    }
    
    /**
     * Busca usuário por email
     */
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null) {
        $stmt = $this->query(
            "SELECT COUNT(*) as total FROM Usuario WHERE email = ? AND idusuario != ?",
            [$email, $excludeId ?? 0]
        );
        $result = $stmt->fetch();
        return $result['total'] > 0;
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
        
        // Mapear campos para banco
        $dbData = [
            'nome' => $data['name'],
            'email' => $data['email'],
            'Senha' => password_hash($data['password'], PASSWORD_DEFAULT),
            'nivel' => $data['role'] ?? 'user',
            'status' => $data['status'] ?? 'Ativo'
        ];
        
        return $this->create($dbData);
    }
    
    /**
     * Atualiza usuário com validação
     */
    public function updateUser($id, $data) {
        $user = $this->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Mapear campos para banco
        $dbData = [];
        
        if (isset($data['name'])) {
            $dbData['nome'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            if (!validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            
            if ($this->emailExists($data['email'], $id)) {
                throw new Exception('Email já está em uso');
            }
            $dbData['email'] = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            if (!validatePassword($data['password'])) {
                throw new Exception('Senha deve ter pelo menos 6 caracteres');
            }
            $dbData['Senha'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $dbData['nivel'] = $data['role'];
        }
        
        if (isset($data['status'])) {
            $dbData['status'] = $data['status'];
        }
        
        return $this->update($id, $dbData);
    }
    
    /**
     * Lista usuários sem senhas
     */
    public function findAllSafe() {
        $users = $this->all();
        foreach ($users as &$user) {
            unset($user['Senha']);
            // Mapear campos
            $user['id'] = $user['idusuario'];
            $user['name'] = $user['nome'];
            $user['role'] = $user['nivel'];
        }
        return $users;
    }
}
