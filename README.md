# MonoModel
Simple active-record style test using static/stateless persistence inside the model.

```php
require_once "vendor/autoload.php";

use MonoModel\User;
use MonoModel\Model;

Model::connect(new PDO('mysql:host=localhost;dbname=dummy', 'root', ''));
for ($i = 1; $i <= 100; $i++) {
    echo User::find($i)->email()."\n";
}

User::findAny(1)->delete();
```
