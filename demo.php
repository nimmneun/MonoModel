<?php
/**
 * @author neun
 * @since  2015-10-25
 */
require_once "vendor/autoload.php";

use MonoModel\User;
use MonoModel\Model;

Model::connect(new PDO('mysql:host=localhost;dbname=dummy', 'root', ''));

echo User::find(3)->email();