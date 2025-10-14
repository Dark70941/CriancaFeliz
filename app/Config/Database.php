<?php

/**
 * Configurações do Banco de Dados
 */
class Database {
    
    // Configurações de conexão
    private static $host = 'localhost';
    private static $dbname = 'criancafeliz';
    private static $username = 'root';
    private static $password = '';
    private static $charset = 'utf8mb4';
    
    // Instância PDO (singleton)
    private static $pdo = null;
    
    /**
     * Obter conexão PDO (singleton)
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=" . self::$charset;
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                self::$pdo = new PDO($dsn, self::$username, self::$password, $options);
                
                error_log('✅ Conexão com banco de dados estabelecida');
                
            } catch (PDOException $e) {
                error_log('❌ ERRO ao conectar ao banco: ' . $e->getMessage());
                throw new Exception('Erro ao conectar ao banco de dados: ' . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * Verificar se banco de dados está disponível
     */
    public static function isAvailable() {
        try {
            self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Executar query diretamente
     */
    public static function query($sql, $params = []) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('❌ Erro na query: ' . $e->getMessage());
            throw new Exception('Erro ao executar query: ' . $e->getMessage());
        }
    }
    
    /**
     * Iniciar transação
     */
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transação
     */
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transação
     */
    public static function rollback() {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Obter último ID inserido
     */
    public static function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Definir usuário logado para triggers
     */
    public static function setLoggedUser($userId) {
        try {
            self::query("SET @usuario_id = ?", [$userId]);
        } catch (Exception $e) {
            error_log('⚠️ Erro ao definir usuário logado: ' . $e->getMessage());
        }
    }
}
