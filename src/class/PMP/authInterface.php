<?php
namespace PMP;

/**
 * Interface AuthInterface
 * @package PMP
 */
interface AuthInterface{
    public function getUserID();
    public function getSalt();
    public function getPassword();
    public function equals(AuthInterface $user);
}
