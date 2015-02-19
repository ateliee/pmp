<?php
namespace PMP;

/**
 * Class DateTime
 * @package PMP
 */
class DateTime extends Date
{

    function __construct($time=null,$default_format='Y-m-d H:i:s')
    {
        parent::__construct($time,$default_format);
    }
}