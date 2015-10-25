<?php
/**
 * @author neun
 * @since  2015-10-25
 */
require_once "vendor/autoload.php";

use MonoModel\Customer;
use MonoModel\Model;

Model::connect(new PDO('mysql:host=localhost;dbname=mono', 'root', ''));

echo Customer::find(1)->firstName();