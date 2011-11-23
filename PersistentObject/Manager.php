<?php
/**
 * @namespace
 */
namespace RedBullet\PersistentObjectsBundle\PersistentObject;

/**
 * Persistent Object Manager
 *
 * @author Kevin Dew <kev@redbullet.co.uk>
 */
class Manager
{
    /**
     * Reg exp for the name of a key
     *
     * @var string
     */
    const NAME_REG_EXP = '#^[a-z0-9_\.]+$#i';

    /**
     * @var string
     */
    private $serializedObjectsPath;

    /**
     * @var array
     */
    private $objectMapping = array();

    /**
     * @var array
     */
    private $objects = array();

    /**
     * @var int
     */
    private $newDirMode;

    /**
     * @var int
     */
    private $newFileMode;

    /**
     * Constructor
     *
     * @param   string  $serializedObjectsPath  Path where serialized objects will be stored
     * @param   array   $objectMapping          Array of keys to objects and object class names
     * @param   int     $newDirMode             What mode serialized object path will be saved with if created
     * @param   int     $newFileMode            What mode new files will be saved with if created
     */
    public function __construct(
        $serializedObjectsPath,
        array $objectMapping,
        $newDirMode = 0755,
        $newFileMode = 0644
    )
    {
        $this->serializedObjectsPath = $serializedObjectsPath;
        $this->objectMapping = $objectMapping;
        $this->newDirMode = $newDirMode;
        $this->newFileMode = $newFileMode;
    }

    /**
     * Get an object from this store
     *
     * @throws  \InvalidArgumentException
     * @param   string  $identifier Key of the object
     * @param   mixed   $default    Value to use if object value is null
     * @return  mixed
     */
    public function get($identifier, $default = null)
    {
        if (!$this->isMapped($identifier)) {
            throw new \InvalidArgumentException('This identifier isn\'t mapped');
        }

        if (!array_key_exists($identifier, $this->objects)) {
            $this->reset($identifier);
        }

        if ($this->objects[$identifier] === null && $default !== null) {
            $this->set($identifier, $default);
        }

        return $this->objects[$identifier];
    }

    /**
     * Set an object to be managed
     *
     * @throws \InvalidArgumentException
     * @param   string  $identifier Key of the object
     * @param   mixed   $object     Object to be managed
     * @return  void
     */
    public function set($identifier, $object = null)
    {
        if (
            ($object !== null)
            &&
            !$this->validObject($identifier, $object)
        ) {
            throw new \InvalidArgumentException('Invalid object type');
        }

        $this->objects[$identifier] = $object;
    }

    /**
     * Persist object to a file
     *
     * Simple wrapper to take an object and serialize it's contents to a file
     *
     * @throws  \InvalidArgumentException|\RunTimeException
     * @param   string  $identifier Key of the object
     * @return  void
     */
    public function persist($identifier)
    {
        if (!$this->isMapped($identifier)) {
            throw new \InvalidArgumentException('This identifier isn\'t mapped');
        }

        $filePath = $this->serializedObjectsPath
            . '/'
            . $this->getPersistPath($identifier)
        ;

        if (!file_exists($this->serializedObjectsPath)) {
            mkdir($this->serializedObjectsPath, $this->newDirMode, true);
        }

        if (!is_dir($this->serializedObjectsPath)) {
            throw new \RunTimeException('Must be a directory');
        }

        if (!is_writeable($this->serializedObjectsPath)) {
            throw new \RunTimeException('Directory must be writable');
        }

        $fileExists = file_exists($filePath);

        $success = file_put_contents(
            $filePath,
            serialize($this->get($identifier))
        );

        if (!$fileExists) {
            chmod($filePath, $this->newFileMode);
        }

        if (!$success) {
            throw new \RunTimeException('Could not save object');
        }
    }

    /**
     * Reset an object back to it's persisted state
     *
     * @throws  \RuntimeException
     * @param   string  $identifier Key of the object
     * @return  void
     */
    public function reset($identifier)
    {
        $object = null;
        $filePath = $this->serializedObjectsPath
            . '/'
            . $this->getPersistPath($identifier)
        ;

        if (file_exists($filePath)) {
            $contents = file_get_contents($filePath);

            if ($contents) {
                $object = @unserialize($contents);

                if ($object === false) {
                    throw new \RuntimeException(
                        'Invalid serialized data in ' . $filePath
                    );
                }

                if ($object && !$this->validObject($identifier, $object)) {
                    throw new \RuntimeException(
                        'Invalid object in serialized data in ' . $filePath
                    );
                }
            }
        }

        $this->objects[$identifier] = $object;
    }

    /**
     * Whether an identifier is mapped to a class
     *
     * @param   string  $identifier Key of the object
     * @return  bool
     */
    public function isMapped($identifier)
    {
        return isset($this->objectMapping[$identifier]);
    }

    /**
     * Get the class name of a mapped identifier
     *
     * @throws  \InvalidArgumentException
     * @param   string  $identifier Key of the object
     * @return  string
     */
    public function getClassName($identifier)
    {
        if (!$this->isMapped($identifier)) {
            throw new \InvalidArgumentException('Identifier not mapped');
        }

        return $this->objectMapping[$identifier];
    }

    /**
     * Whether an object exists for a mapping
     *
     * @param   string  $identifier Key of the object
     * @return  bool
     */
    public function exists($identifier)
    {
        return $this->get($identifier) !== null;
    }

    /**
     * Get the path to where to persist an object
     *
     * @throws  \InvalidArgumentException
     * @param   string  $identifier Key of the object
     * @return  string
     */
    protected function getPersistPath($identifier)
    {
        if (!preg_match(self::NAME_REG_EXP, $identifier)) {
            throw new \InvalidArgumentException(
                'Object identifier must be alpha-numeric with only _ and . characters'
            );
        }

        return $identifier . '.php.meta';
    }

    /**
     * Whether object is of the correct class
     *
     * @throws  \InvalidArgumentException
     * @param   string  $identifier identifier for the object
     * @param   mixed   $object     Object to be checked
     * @return  bool
     */
    protected function validObject($identifier, $object)
    {
        if (!$this->isMapped($identifier)) {
            throw new \InvalidArgumentException('This identifier isn\'t mapped');
        }

        $class = $this->getClassName($identifier);

        return $object instanceof $class;
    }
}