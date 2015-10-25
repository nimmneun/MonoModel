<?php namespace MonoModel;

/**
 * @author neun
 */
class User extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @return int $id
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string $alias
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return User
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string $email
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string $password
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = hash('sha256', $password);
        return $this;
    }

    /**
     * Unnecessary - just for IDE code completion.
     *
     * @param int $id
     * @return User
     */
    public static function find($id)
    {
        return parent::find($id);
    }

    /**
     * Unnecessary - just for IDE code completion.
     *
     * @param int $id
     * @return User
     */
    public static function findTrashed($id)
    {
        return parent::findTrashed($id);
    }

    /**
     * Unnecessary - just for IDE code completion.
     *
     * @param int $id
     * @return User
     */
    public static function findAny($id)
    {
        return parent::findAny($id);
    }
}
