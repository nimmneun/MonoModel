# MonoModel

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nimmneun/MonoModel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nimmneun/MonoModel/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/nimmneun/MonoModel/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nimmneun/MonoModel/build-status/master)

Simple active-record style test using static/stateless persistence inside the model.

```php
require_once "vendor/autoload.php";

use MonoModel\User;
use MonoModel\Model;

Model::connect(new PDO('mysql:host=localhost;dbname=dummy', 'root', ''));

echo User::findByEmail('ency@fender.com')->email();

for ($i = 1; $i <= 100; $i++) {
    echo User::find($i)->email()."\n";
}

User::findBy(['alias' => 'Novacaine'])->delete();

foreach (User::findAllBy(['is_deleted' => '1']) as $user) {
    $user->restore();
}
```
