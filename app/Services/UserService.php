<?php

/**
 * Service para gerenciamento de usuários
 */
class UserService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Obtém todos os usuários
     */
    public function getAllUsers() {
        return $this->userModel->getAll();
    }
    
    /**
     * Obtém usuário por ID
     */
    public function getUser($id) {
        $user = $this->userModel->findById($id);
        if ($user) {
            // Mapear campos MySQL para formato esperado
            $user['id'] = $user['id'] ?? $user['idusuario'];
            $user['name'] = $user['name'] ?? $user['nome'];
            $user['role'] = $user['role'] ?? $user['nivel'];
            unset($user['Senha']); // Remover senha
        }
        return $user;
    }
    
    /**
     * Cria novo usuário
     */
    public function createUser($data) {
        // Verificar se email já existe
        if ($this->userModel->findByEmail($data['email'])) {
            throw new Exception('Email já está em uso');
        }
        
        // Preparar dados
        $userData = [
            'id' => uniqid('user_'),
            'name' => sanitizeInput($data['name']),
            'email' => sanitizeInput($data['email']),
            'password' => $data['password'], // Será hasheado no model
            'role' => $data['role'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->userModel->create($userData);
    }
    
    /**
     * Atualiza usuário
     */
    public function updateUser($id, $data) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Verificar se email já existe (exceto para o próprio usuário)
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] !== $id) {
            throw new Exception('Email já está em uso');
        }
        
        // Preparar dados
        $userData = [
            'name' => sanitizeInput($data['name']),
            'email' => sanitizeInput($data['email']),
            'role' => $data['role'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Adicionar senha se fornecida
        if (!empty($data['password'])) {
            $userData['password'] = $data['password']; // Será hasheado no model
        }
        
        return $this->userModel->update($id, $userData);
    }
    
    /**
     * Exclui usuário
     */
    public function deleteUser($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        return $this->userModel->delete($id);
    }
    
    /**
     * Alterna status do usuário
     */
    public function toggleUserStatus($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        
        $this->userModel->update($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['status' => $newStatus];
    }
    
    /**
     * Obtém estatísticas de usuários
     */
    public function getUserStats() {
        $users = $this->userModel->getAll();
        
        $stats = [
            'total' => count($users),
            'active' => 0,
            'inactive' => 0,
            'by_role' => [
                'admin' => 0,
                'psicologo' => 0,
                'funcionario' => 0
            ]
        ];
        
        foreach ($users as $user) {
            if ($user['status'] === 'active') {
                $stats['active']++;
            } else {
                $stats['inactive']++;
            }
            
            if (isset($stats['by_role'][$user['role']])) {
                $stats['by_role'][$user['role']]++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Obtém nome do papel/role em português
     */
    public function getRoleName($role) {
        $roles = [
            'admin' => 'Administrador',
            'psicologo' => 'Psicólogo',
            'funcionario' => 'Funcionário'
        ];
        
        return $roles[$role] ?? 'Desconhecido';
    }
    
    /**
     * Obtém descrição das permissões por role
     */
    public function getRolePermissions($role) {
        $permissions = [
            'admin' => 'Acesso total ao sistema, gerenciamento de usuários e configurações',
            'psicologo' => 'Visualização de fichas, área exclusiva de anotações psicológicas',
            'funcionario' => 'Apenas visualização de informações, sem edição'
        ];
        
        return $permissions[$role] ?? 'Sem permissões definidas';
    }
}
