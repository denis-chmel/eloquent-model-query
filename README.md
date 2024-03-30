# Eloquent Model Query

Adds touch of type-strictness to Laravel/Eloquent queries.

This component is essentially making it easy to do what is suggested here:
https://timacdonald.me/dedicated-eloquent-model-query-builders/

The problem it solves - bypasses the lack of auto-completion in Eloquent builder/query. While the suggested solution allows to declare dedicated query class, one per each model class.

## Installation
Add into composer.json
```
    "repositories": {
        "eloquent-query-filter": {
            "type": "vcs",
            "url": "git@github.com:denis-chmel/eloquent-model-query.git"
        }
    }
```
And then run:
```
composer require denis-chmel/eloquent-model-query:@dev
```

After that use HasModelQuerySupport trait and override the query() method in your Model:
```
<?php

namespace App\Model\User;

class User extends \Eloquent {

    use \EloquentModelQuery\HasModelQuerySupport;

// ...

    public static function query(): UserQuery
    {
        return UserQuery::getInstance(parent::query());
    }

// ...
}
```

Where UserQuery is set of your custom DRY methods like:
```
<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use EloquentModelQuery\AbstractQuery;

/**
 * @method User|null first()
 * @method User firstOrFail()
 * @method User findOrFail($id, $columns = ['*'])
 * @method Collection|User[] get($columns = ['*'])
 */
class UserQuery extends AbstractQuery
{
    public function name(string $value): self
    {
        return $this->where('name', '=', $value);
    }

    public function emailEndsWith(string $needle): self
    {
        return $this->where('email', 'LIKE', $needle . '%');
    }
}
```

After that the usage is
```
$users = User::query()->name('Denis')->emailEndsWith('@gmail.com')->get();
$count = User::query()->name('Denis')->emailEndsWith('@gmail.com')->count();
$emails = User::query()->name('Denis')->emailEndsWith('@gmail.com')->pluck('email);
```
In other words - use same Eloquent query approach, but with autocomplete. The benefit - the autocompletion and refactor/rename/go to definition/find all usages works as they should in all strict-typed languages, no magic, no phpdoc workarounds.

Lastly, there is one more sugar with eloquent-model-query, you may reuse your method and *typehint* your custom queries in sub-queries, like see the next example that is a query of a Order model, that subqueries order users using UserQuery:

```
class OrderQuery extends \EloquentCustomQuery\AbstractQuery {

    public function suspicious(): self
    {
        $this->whereAny(static function (OrderQuery $q) { // makes inside wheres have OR logic
            $q->afterDate(now());
            $q->beforeDate(now()->addMonth());
            return $q;
        });
        $this->whereHas('users', static function(UserQuery $q) { // <-- notice UserQuery type
            // $q is UserQuery here, so auto-completion & refacttoring works 
            return $q
                ->name('Denis)
                ->emailEndsWith('@gmail.com');
        });
        return $this;
    }

    public function afterDate(Date $date): self {
        return $this->where('created_at', '>=', $date);
    }

    public function beforeDate(Date $date): self {
        return $this->where('created_at', '<', $date);
    }
```
