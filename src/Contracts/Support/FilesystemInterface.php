<?php

namespace Fenix\Contracts\Support;

/**
 * @author  Neverson Bento da Silva <neversonbs13@gmail.com>
 */
interface FilesystemInterface
{
    /**
     *  Get the extension of the file
     *
     * @return string
     */
    public function getExtension() : string;

    /**
     * Set the file extension
     * @param string $ext
     * @return mixed
     */
    public function setExtension($ext);

    /**
     * Set the files path
     *
     * @param string $path
     * @return void
     */
    public function setPath($path);

    /**
     * Get the files path
     * @return string
     */
    public function getPath() : string;

    /**
     * Set the filename
     *
     * @param $name
     * @return void
     */
    public function setName($name);

    /**
     * Get the filename
     *
     * @return string
     */
    public function getName();

    /**
     * Set a content
     *
     * @param $content
     * @return mixed
     */
    public function setContent(string $content);

    /**
     * Get the file content
     *
     * @return mixed
     */
    public function getContent();

    /**
     * Check if file exists
     *
     * @return bool
     */
    public function isFile() : bool;
    /**
     * Last time edited
     *
     * Returns unix time for last modification
     *
     * @return int The unix time
     */
    public function lastModification() : int;

    /**
     * Check if the file has recent modification
     *
     * @return boolean
     */
    public function lastEditedIn($time): bool;

    /**
     * Set the content of a file
     *
     * @return void
     */
    public function save($name, $options);

    /**
     * Write the content in the file
     *
     * If the file doesn't exist, must to create a new one
     * @param string $filename
     * @return boolean
     */
    public function write($filename);

    /**
     * Read the file content
     * @return $this|string
     */

    public function read();


    public function noContent();
}