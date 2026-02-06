<?php

declare(strict_types=1);

class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];

        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }

    public function id(): ?int
    {
        return $this->check() ? $_SESSION['user_id'] : null;
    }

    public function user(): ?array
    {
        $id = $this->id();
        if ($id === null) {
            return null;
        }

        return $this->users->findById($id);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
