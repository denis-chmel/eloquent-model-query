<?php declare(strict_types=1);

namespace EloquentModelQuery;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * To be added to your BaseModel (or any model class)
 */
trait HasModelQuerySupport
{
    /*
     * No typehints cause Illuminate\Database\Eloquent\Model::newEloquentBuilder($query) has no as well
     * (so avoiding warnings about declaration issues)
     */
    public function newEloquentBuilder($query)
    {
        $builderClass = EloquentBuilder::class;
        try {
            // If model declares "public static function query(): MyFilter" we create instance of MyFilter,
            // if query() is not overridden - use the EloquentBuilder - Laravel's default query() result
            $class = new \ReflectionClass(static::class);
            if ($returnType = $class->getMethod('query')->getReturnType()) {
                $builderClass = $returnType->getName();
            }
        } catch (\ReflectionException $e) {
            // Swallow and use the default class
        }
        return new $builderClass($query);
    }
}
