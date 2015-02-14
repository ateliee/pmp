<?php
namespace PMP;

/**
 * Class DateTime
 * @package PMP
 */
class DateTime extends Date
{
    function __toString()
    {
        return $this->getDateTime();
    }
}