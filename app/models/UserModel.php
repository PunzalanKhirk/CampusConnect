<?php
class UserModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /** Look up an active user by email. Used only for login. */
    public function findByEmail(string $email): array|false
    {
        $this->db->query('SELECT id, full_name, email, password_hash, role, is_active
                           FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $row ?: false;
    }

    public function findById(int $id): array|false
    {
        $this->db->query('SELECT id, full_name, email, role, is_active FROM users WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ?: false;
    }
}
