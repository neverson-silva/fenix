<?php
namespace Fenix\Support;

use Fenix\Contracts\Support\FilesystemInterface;
use InvalidArgumentException;
use Exception;

class File implements FilesystemInterface
{

    protected $path;

    protected $name;

    protected $extension;

    protected $newName;

    protected $content;

    public function __construct($path, $name = '', $ext = '')
    {
        $this->setPath($path);
        $this->setName($name);
        $this->setExtension($ext);
    }

    /**
     * Clear content
     */
    public function clear()
    {
        $this->content = null;
        $this->name = null;
        $this->newName = null;
    }

    /**
     * Set a new name
     *
     * Set a new name to be use when writing the file
     *
     * @param $newName
     */
    public function newName($newName)
    {
        $this->newName = $newName;
    }

    /**
     * Get new file name
     * @return string
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     *  Get the extension of the file
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Set the file extension
     * @param string $ext
     * @return mixed
     */
    public function setExtension($ext)
    {
        if (!empty($ext)){
            $this->extension = $ext;
        }
    }

    /**
     * Set the files path
     *
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        if (!is_dir($path)){
            throw new InvalidArgumentException("The path '$path' is not a valid directory");
        }
        $this->path = $this->replaceAndRemoveExtraSlash($path);//str_replace('.', DIRECTORY_SEPARATOR, $path);
    }

    private function checkTwoDots($string)
    {        
        return preg_match("/(.*\.)+.*/", $string);
    }

    private function replaceAndRemoveExtraSlash(string $string)
    {
        if ($this->checkTwoDots($string)) {
            return $string;
        }
        $string = $this->replaceDotForSlash($string);

        return $this->removeExtraSlash($string);
    }

    private function replaceDotForSlash($string)
    {
        return str_replace('.', DIRECTORY_SEPARATOR, $string);
    }

    private function removeExtraSlash(string $path)
    {
        $values = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($values as $key => $value) {
            if (empty($value)) {
                unset($values[$key]);
            }
        }
        return implode(DIRECTORY_SEPARATOR, $values);
    }

    /**
     * Get the files path
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the filename
     *
     * @param $name
     * @return void
     */
    public function setName($name)
    {
        if (strpos($name, '.')) {
            $this->name = str_replace('.', DIRECTORY_SEPARATOR, $name);
        } else {
            $this->name = empty($name) ? null : $name;
        }
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Last time edited
     *
     * Returns unix time for last modification
     * @throws Exception If there is no valid file
     * @return int The unix time
     */
    public function lastModification(): int
    {
        if (!is_null($this->newName)) {
            return filemtime($this->fullNewName());
        }
        if (!$this->isFile()) {
            throw new Exception("There is no valid file to check last time modified.");
        }

        return filemtime($this->fullName());
    }

    /**
     * Check if the file has recent modification
     *
     * @return boolean
     * @throws Exception
     */
    public function lastEditedIn($time): bool
    {
        return $this->lastModification() > strtotime("-$time");
    }

    /**
     * Set the content of a file
     *
     * @return $this
     * @throws FileNotFoundException If the file doesnt exists
     */
    public function read()
    {
        if ($this->isFile()) {

            $content = file_get_contents($this->fullName());

            $this->setContent($content);

            return $this;

        }
        $file = $this->fullName();
        throw new FileNotFoundException("File '$file' not found.");
    }

    /**
     * Write the content in the file
     *
     * If the file doesn't exist, must to create a new one
     * @param string $filename
     * @return boolean
     */
    public function write($filename)
    {
        // TODO: Implement write() method.
    }

    public function fullName()
    {
        if ($this->isValid()) {
            return $this->getPath() . DIRECTORY_SEPARATOR . $this->getName() . '.' .$this->getExtension();
        }
        return null;
    }

    public function fullNewName()
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $this->getNewName() . '.' .$this->getExtension();
    }

    /**
     * Check if class has valid properties
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->path) && !empty($this->name) && !empty($this->extension);
    }

    /**
     * Check the directory and file passed if a valid file
     *
     * @return bool
     *
     */
    public function isFile($useNewName = false) : bool
    {
        if ($useNewName) {
            return is_file($this->fullNewName());
        }
        return is_file($this->fullName());
        //throw new \Exception("Invalid directory or filename.");
    }

    public function save($name = null, $dontUseFullName = false, $content = null, $options = 'w+')
    {

        if (!$name && !$dontUseFullName && !$content) {
            $name = $this->fullName();
        } else {
            $name = $this->getPath() . DIRECTORY_SEPARATOR . $name . '.' . $this->getExtension();

            if ($dontUseFullName == false) {
                if (!is_null($name)) $this->newName($name);

                if (!is_null($this->newName)) {
                    $name = $this->fullNewName();
                } else {
                    $name = $this->fullName();
                }
            }
        }

        $content = $content ?? $this->getContent();
        $writer = fopen($name, $options);
        fwrite($writer, $content);
        fclose($writer);
        return $name;

    }

    /**
     * Check if the class has read the file.
     *
     * @return bool If content property is empty
     */
    public function noContent()
    {
        return is_null($this->content) || empty($this->content);
    }

    public function noName()
    {
        return is_null($this->name) || empty($this->name);
    }
}