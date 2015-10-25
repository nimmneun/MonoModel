<?php
/**
 * @author neun
 * @since  2015-10-25
 */
require_once "vendor/autoload.php";

use MonoModel\User;
use MonoModel\Model;

Model::connect(new PDO('mysql:host=localhost;dbname=dummy', 'root', ''));

for ($i = 1; $i <= 100; $i++) {
    echo User::find($i)->email()."\n";
}

echo User::findByEmail('hamsolo@nut.com')->alias();
