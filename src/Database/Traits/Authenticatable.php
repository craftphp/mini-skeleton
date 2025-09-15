<?php
namespace Craft\Database\Traits;

/**
 * #### Authenticatable trait
 * 
 * This trait provides functionality to mark an object as authenticatable,
 * allowing it to be used in authentication processes.
 */
trait Authenticatable
{
    /**
     * The timestamp when the object was authenticated
     *
     * @var string|null
     */
    protected $authenticated_at = null;

    /**
     * Mark the object as authenticated.
     *
     * @return $this
     */
    public function authenticate()
    {
        $this->authenticated_at = date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Check if the object is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return !is_null($this->authenticated_at);
    }

    /**
     * The password hash for the object.
     *
     * @var string|null
     */
    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function generateAuthToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->auth_token = $token;
        $this->token_expires_at = date('Y-m-d H:i:s', time() + 3600);
        $this->save();
        return $token;
    }

    public function checkToken(string $token): bool
    {
        return $this->auth_token === $token && strtotime($this->token_expires_at) > time();
    }

    public function logout(): void
    {
        $this->auth_token = null;
        $this->token_expires_at = null;
        $this->save();
    }
}