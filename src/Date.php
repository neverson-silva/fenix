<?php

namespace Fenix;

use DateTime;
use DateTimeZone;
use ReflectionObject;


class Date extends DateTime
{
    private $fusoHorario = 'America/Sao_Paulo';

    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($time, new DateTimeZone($this->fusoHorario));
    }

    public function setFusoHorario(string $fusoHorario)
    {
        $this->fusoHorario = $fusoHorario;
    }

    public function getFusoHorario()
    {
        return $this->fusoHorario;
    }

    /**
     * Get the current year
     * @return int
     */
    public static function currentYear()
    {
        return (int) date('Y');
    }

    /**
     * Get the current month
     * @return int
     */
    public static function currentMonth()
    {
        return (int) date('m');
    }

    /**
     * Get the current day
     * @return int
     */
    public static function currentDay()
    {
        return (int) date('d');
    }

    public function __toString()
    {
        return $this->format('d/m/Y');
    }

    public function getDate()
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function getFormated()
    {
        return $this->format('d/m/Y');
    }

    public function getYear()
    {
        $new = clone $this;

        return $new->format('Y');
    }

}
