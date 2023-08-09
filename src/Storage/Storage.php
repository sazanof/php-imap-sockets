<?php

namespace Sazanof\PhpImapSockets\Storage;

use Sazanof\PhpImapSockets\Exceptions\StorageException;

/**
 * @method static save(string $filename, string $contents = '')
 * @method static delete(string $filename)
 * @method static copy(string $filenameFrom, string $fileNameTo)
 * @method static move(string $filenameFrom, string $fileNameTo)
 * @method static chmod(string $filename, string $mode)
 */
abstract class Storage
{
	protected string $root;
	protected string $path;
	protected string $filename;
	protected static self|null $instance = null;

	/**
	 * @param string $path
	 * @throws StorageException
	 */
	public function __construct(string $path)
	{
		if (empty($path)) {
			$path = realpath(dirname('', 2));
		}
		$this->setRoot($path);
		if (!file_exists($this->getRoot())) {
			$this->createDirectory($path);
		}
		if (!is_writable($this->getRoot())) {
			throw new StorageException("Directory $path is not writable");
		}

	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return void
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$name = $name . 'File';
		self::$instance->$name(...$arguments);
	}

	public static function in(string $path)
	{
		self::$instance = is_null(self::$instance) ? new static($path) : self::$instance;
		return self::$instance;
	}

	public static function pathInfo(string $path)
	{
		return pathinfo($path);
	}

	/**
	 * @return string
	 */
	public function getRoot(): string
	{
		return $this->root;
	}

	/**
	 * @param string $root
	 */
	public function setRoot(string $root): void
	{
		$this->root = $root;
	}

	/**
	 * @param string $name
	 * @param string $permissions
	 * @return bool
	 */
	public function createDirectory(string $name, string $permissions = '0755'): bool
	{
		return mkdir($this->getRoot(), $permissions);
	}

	/**
	 * @param string $filename
	 * @param string $contents
	 * @return bool|int
	 */
	public function createFile(string $filename, string $contents = ''): bool|int
	{
		$fullPath = $this->getRoot() . DIRECTORY_SEPARATOR . $filename;
		return file_put_contents($fullPath, $contents);
	}

	abstract public function saveFile();
}
