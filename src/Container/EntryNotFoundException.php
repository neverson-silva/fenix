<?php
namespace Fenix\Container;

use Psr\Container\NotFoundExceptionInterface;
use Exception;
/**
 * Description of EntryNotFoundException
 *
 * @author Neverson Silva
 */
class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
}