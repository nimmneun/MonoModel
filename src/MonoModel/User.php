<?php namespace MonoModel;

/**
 * @author neun
 * @method static User|null find($id)
 * @method static User|null findAny($id)
 * @method static User|null findTrashed($id)
 * @method static User|null findBy(array $columns)
 * @method static User[]|[] findAllBy(array $columns)
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
     * Actual model-specific method to find the object.
     *
     * @param string $email
     * @return User
     */
    public static function findByEmail($email)
    {
        return parent::findBy(['email' => $email]);
    }
}
