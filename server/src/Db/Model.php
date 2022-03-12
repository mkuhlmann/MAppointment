<?php

namespace App\Db;

use Exception;
use InvalidArgumentException;
use League\Container\Exception\ContainerException;
use League\Container\Exception\NotFoundException;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyStatement;
use ParagonIE\EasyDB\Exception\QueryError;
use TypeError;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Rakit\Validation\Rules\Nullable;

class Model implements \ArrayAccess, \JsonSerializable 
{
	protected static string $table;
	protected static string $primaryKey = 'id';

	protected $exists = false;

	protected array $attributes = [];

	protected array $original = [];
	protected array $changes = [];

	protected array $cachedRelations = [];

	 /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
	protected array $fillable = [];

	/**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
	protected array $guarded = ['*'];

	public function __construct(array $attributes = [])
	{
		$this->fill($attributes);
	}

	public function jsonSerialize(): mixed { 
		return $this->attributes;
	}

	public function hasAttribute(string $name): bool
	{
		return isset($this->attributes[$name]);
	}

	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
		$this->changes[$key] = $value;
	}

	public function getAttribute($key)
	{
		return $this->attributes[$key];
	}

	public function isFillable($key) {
		if(in_array($key, $this->fillable)) {
			return true;
		}

		if($this->guarded == ['*']) {
			return false;
		}

		return !in_array($key, $this->guarded);
	}

	public function fill(array $attributes)
	{
		foreach ($attributes as $key => $value) {
			if($this->isFillable($key)) {
				$this->setAttribute($key, $value);
			}
		}
	}

	public function isDirty()
	{
		return count($this->changes) > 0;
	}

	public function isNew()
	{
		return !$this->exists;
	}

	/** Relationships */
	public function belongsTo($className, $foreignKey = null, $ownerKey = null)
	{
		if(isset($this->cachedRelations[$className])) {
			return $this->cachedRelations[$className];
		}

		$ownerKey = $ownerKey ?? $className::$primaryKey;

		$classNameShort = (new \ReflectionClass($className))->getShortName();
		$foreignKey = $foreignKey ?? strtolower($classNameShort) . 'Id';

		$this->cachedRelations[$className] = $className::first(QueryBuilder::open()->where($ownerKey, $this->getAttribute($foreignKey)));
		return $this->cachedRelations[$className];
	}

	public function hasMany($className, $foreignKey = null, $ownerKey = null)
	{
		if(isset($this->cachedRelations[$className])) {
			return $this->cachedRelations[$className];
		}

		$owerKey = $ownerKey ?? $className::$primaryKey;

		$classNameShort = (new \ReflectionClass(static::class))->getShortName();
		$foreignKey = $foreignKey ?? $classNameShort . 'Id';

		$this->cachedRelations[$className] = $className::all(QueryBuilder::open()->with($foreignKey, $this->getAttribute($foreignKey)));
		return $this->cachedRelations[$className];
	}

	/** CURD */

	public function save()
	{
		if ($this->exists) {
			$this->update();
		} else {
			$this->insert();
		}
	}

	public function insert()
	{
		if (!isset($this->attributes[static::$primaryKey])) {
			$this->attributes[static::$primaryKey] = nanoid();
		}

		$this->exists = true;
		$this->original = $this->attributes;
		$this->changes = [];
		static::getDb()->insert(static::getTableName(), $this->attributes);
	}

	public function update()
	{
		if (!$this->isDirty()) {
			return;
		}
		if (!$this->exists) {
			throw new \Exception('Cannot update a model that does not exist');
		}

		static::getDb()->update(static::getTableName(), $this->changes, [static::$primaryKey => $this->attributes[static::$primaryKey]]);

		$this->original = $this->attributes;
		$this->changes = [];
	}

	public function delete()
	{
		static::getDb()->delete(static::getTableName(), [static::$primaryKey => $this->attributes[static::$primaryKey]]);
	}

	/** Magic methods */

	public function __get($name)
	{
		if (array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}

		if(method_exists($this, $name)) {
			return $this->$name();
		}

		return null;
	}

	public function __set($name, $value)
	{
		$this->setAttribute($name, $value);
	}

	public function __isset($name)
	{
		return $this->hasAttribute($name);
	}

	public function __unset($name)
	{
		unset($this->attributes[$name]);
	}

	public function __toString()
	{
		return json_encode($this->attributes);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->setAttribute($offset, $value);
	}

	public function offsetExists(mixed $offset): bool
	{
		return $this->hasAttribute($offset);
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->attributes[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->getAttribute($offset);
	}

	/**
	 * STATIC METHODS
	 */

	public static function getTableName()
	{
		$className = (new \ReflectionClass(static::class))->getShortName();
		return static::$table ??  strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $className)) . 's';
	}

	private static function fromRow(array|null $row)
	{
		if (empty($row) || $row == null) {
			return null;
		}

		$model = new static();
		$model->exists = true;
		$model->original = $row;
		$model->attributes = $row;
		return $model;
	}


	public static function getDb(): EasyDB
	{
		return \App\Application::getInstance()->get(EasyDB::class);
	}


	public static function where(EasyStatement|array $where): EasyStatement
	{
		if ($where instanceof EasyStatement) {
			return $where;
		}

		$statement = EasyStatement::open();
		foreach ($where as $key => $value) {
			$statement->with($key, $value);
		}

		return $statement;
	}

	public static function first(EasyStatement $where)
	{
		$statement = static::where($where);
		$row = static::getDb()->row('SELECT * FROM ' . static::getTableName() . ' WHERE ' . $statement->sql(), ...$statement->values());
		return self::fromRow($row);
	}

	public static function all(EasyStatement $where = null)
	{
		$statement = static::where($where);
		$rows = static::getDb()->run('SELECT * FROM ' . static::getTableName() . ' WHERE ' . $statement->sql(), ...$statement->values());

		return array_map(function ($row) {
			return self::fromRow($row);
		}, $rows);
	}

	/**
	 * 
	 * @param mixed $id 
	 * @return null|static Model
	 */
	public static function find($id)
	{
		$db = self::getDb();
		return static::fromRow($db->row('SELECT * FROM ' . static::getTableName() . ' WHERE ' . static::$primaryKey . ' = ?', $id));
	}

	public static function findOrFail($id) {
		$model = static::find($id);
		if(!$model) {
			throw new \Exception('Model not found');
		}
		return $model;
	}


	/**
	 * Creates new model and saves to database
	 * 
	 * @param array $attributes 
	 * @return static Model
	 */
	public static function create(array $attributes)
	{
		$model = new static();
		$model->fill($attributes);
		$model->save();
		return $model;
	}
}
