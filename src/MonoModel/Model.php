<?php namespace MonoModel;

/**
 * Attempting a model with static/stateless persistence
 * contained within itself. Snake case properties to
 * allow using PDO's fetchObject() while retaining
 * the visibility of the actual models properties.
 * todo: Think of a nifty way to load and save
 * complex objects.
 *
 * @author neun
 */
abstract class Model
{
    protected $id;
    protected $created_at;
    protected $created_by;
    protected $updated_at;
    protected $updated_by;
    protected $is_deleted;
    protected $hash;

    /**
     * @var \PDO
     */
    protected static $db;

    /**
     * @var array of all properties to ignore
     * while persisting any kind of model.
     */
    protected $globalIgnores = [
        'globalIgnores' => true,
        'ignores' => true,
        'hash' => true,
        'db' => true,
    ];

    /**
     * @var array of properties to ignore
     * while persisting the current model.
     */
    protected $ignores = [];

    /**
     * @return int
     */
    abstract public function id();

    /**
     * @param int $id
     * @return Model
     */
    abstract public function setId($id);

    /**
     * @return string
     */
    public function createdAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return Model
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return int
     */
    public function createdBy()
    {
        return $this->created_by;
    }

    /**
     * @param int $created_by
     * @return Model
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * @return string
     */
    public function updatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param string $updated_at
     * @return Model
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @return int
     */
    public function updatedBy()
    {
        return $this->updated_by;
    }

    /**
     * @param int $updated_by
     * @return Model
     */
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return (bool)$this->is_deleted;
    }

    /**
     * Soft delete current model.
     */
    public function delete()
    {
        $this->is_deleted = '1';
        $this->save();
    }

    /**
     * Restore the current soft deleted model.
     */
    public function restore()
    {
        $this->is_deleted = '0';
        $this->save();
    }

    /**
     * Persist existing model with new update timestamp.
     */
    public function touch()
    {
        $this->id ?: $this->setUpdatedAt(null)->save();
    }

    /**
     * Return properties of the current model.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Return the cuurent model as JSON string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Public interface to persist the Model.
     */
    public function save()
    {
        static::persist($this);
    }

    /**
     * Find and return the model by it's id.
     *
     * @param $id
     * @return Model
     */
    public static function find($id)
    {
        return static::findBy(['id' => $id, 'is_deleted' => 0]);
    }

    /**
     * Find and return soft deleted model by it's id.
     *
     * @param $id
     * @return Model
     */
    public static function findTrashed($id)
    {
        return static::findBy(['id' => $id, 'is_deleted' => 1]);
    }

    /**
     * Find and return any model by it's id.
     *
     * @param $id
     * @return Model
     */
    public static function findAny($id)
    {
        return static::findBy(['id' => $id]);
    }

    /**
     * Find and return the requested model by
     * constraints specified in $columns.
     *
     * @param array $columns
     * @return Model|null
     * @throws \Exception
     */
    public static function findBy(array $columns)
    {
        static::connect();

        $str = "SELECT\n  *\nFROM\n  `%s`\nWHERE %s";
        $sql = sprintf($str,
            static::tabelize(get_called_class()),
            static::wheres($columns)
        );
        $stm = static::$db->query($sql);
        $model = $stm->fetchObject(get_called_class());

        if ($model) {
            static::rehash($model);
        }

        return $model ? $model : null;
    }

    /**
     * Find and return an array of all the requested
     * models by constraints specified in $columns.
     * Optionally limit the number of results.
     *
     * @param array $columns
     * @param int $limit
     * @return Model[]
     * @throws \Exception
     */
    public static function findAllBy(array $columns, $limit = null)
    {
        static::connect();

        $limit = $limit ? 'LIMIT '.(int)$limit : null;
        $str = "SELECT\n  *\nFROM\n  `%s`\nWHERE %s\n".$limit;
        $sql = sprintf($str,
            static::tabelize(get_called_class()),
            static::wheres($columns)
        );
        $stm = static::$db->query($sql);

        while($model = $stm->fetchObject(get_called_class())) {
            if ($model) {
                static::rehash($model);
            }
            $models[] = $model;
        }

        return isset($models) ? $models : [];
    }

    /**
     * Build where clause based on the constraints
     * specified within the associative array.
     *
     * @param array $columns
     * @return string
     */
    protected static function wheres(array $columns)
    {
        foreach ($columns as $field => $value) {
            $wheres[] = "\n  ".'`'.$field.'` = '.static::$db->quote($value);
        }

        return isset($wheres) ? implode(' AND ', $wheres) : 1;
    }

    /**
     * Persist (insert/update) the given model.
     *
     * @param Model $model
     * @return Model
     */
    protected static function persist(Model $model)
    {
        if (static::isDirty($model) || 1 > $model->id) {

            static::connect();
            static::fillTimestamps($model);

            if (1 > $model->id) {
                static::insert($model);
            } else {
                static::update($model);
            }

            static::rehash($model);
        }
    }

    /**
     * Perform update query for the given model.
     *
     * @param Model $model
     */
    protected static function update(Model $model)
    {
        $str = "UPDATE\n  `%s`\nSET%s\nWHERE\n  `id` = %s";
        $sql = sprintf($str,
            static::tabelize($model),
            static::updatables($model),
            static::$db->quote($model->id)
        );

        static::$db->query($sql);
    }

    /**
     * Extract and escape columns and values from the
     * given model and return them as single string.
     *
     * @param Model $model
     * @return string
     */
    protected static function updatables(Model $model)
    {
        foreach ($model->toArray() as $column => $value) {
            if (!isset($model->globalIgnores[$column])
                && !isset($model->ignores[$column])
            ) {
                $updates[] = "\n  ".'`'.$column.'` = '.self::$db->quote($value);
            }
        }

        return isset($updates) ? implode(',', $updates) : '`id`=`id`';
    }

    /**
     * Perform insert query for the given model.
     *
     * @param Model $model
     */
    protected static function insert(Model $model)
    {
        $str = "INSERT INTO\n  `%s`\n  (%s)\nVALUES\n  (%s)";
        list($columns, $values) = static::insertables($model);
        $sql = sprintf($str,
            static::tabelize($model),
            $columns,
            $values
        );

        if (static::$db->query($sql)) {
            $model->id = static::$db->lastInsertId();
        }
    }

    /**
     * Extract and escape all columns and values from the
     * model & return an array containing both strings.
     *
     * @param Model $model
     * @return string[]
     */
    protected static function insertables(Model $model)
    {
        foreach ($model->toArray() as $column => $value) {
            if (!isset($model->globalIgnores[$column])
                && !isset($model->ignores[$column])
            ) {
                $columns[] = '`'.$column.'`';
                $values[] = static::$db->quote($value);
            }
        }

        return isset($columns) && isset($values)
            ? array(implode(',',$columns), implode(',',$values))
            : array();
    }

    /**
     * Init/inject PDO connection, if there is none yet.
     *
     * @param \PDO $pdo
     * @throws \Exception
     */
    public static function connect(\PDO $pdo = null)
    {
        static::$db = static::$db ?: $pdo;

        if (!static::$db instanceof \PDO) {
            throw new \Exception("Missing PDO connection");
        }
    }

    /**
     * (Re)set the hash value for the given model.
     *
     * @param Model $model
     */
    protected static function rehash(Model $model)
    {
        $model->hash = null;
        $model->hash = md5($model);
    }

    /**
     * Check for changes within the given model by comparing
     * it's current hash value with a newly generated one.
     *
     * @param Model $model
     * @return bool
     */
    protected static function isDirty(Model $model)
    {
        $clone = clone $model;
        $clone->hash = null;

        return md5($clone) !== $model->hash;
    }

    /**
     * Update/set the timestamp(s) of a given model.
     *
     * @param Model $model
     */
    protected static function fillTimestamps(Model $model)
    {
        if (1 > $model->id) {
            $model->created_at = date('Y-m-d H:i:s');
        }
        $model->updated_at = date('Y-m-d H:i:s');
    }

    /**
     * Turn (namespaced) class name into a valid table name.
     *
     * @param object|string $class
     * @return string
     */
    protected static function tabelize($class)
    {
        $rc = new \ReflectionClass($class);
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $rc->getShortName())), '_');
    }
}
