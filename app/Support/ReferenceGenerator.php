<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class ReferenceGenerator
{
    public static function generate(string $model, string $prefix): string
    {
        $prefix = strtoupper($prefix);

        do {
            $reference = sprintf('%s-%s-%04d', $prefix, now()->format('ymd'), random_int(0, 9999));
        } while ($model::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public static function ensure(Model $model, string $prefix): string
    {
        if ($model->reference) {
            return $model->reference;
        }

        $model->reference = self::generate($model::class, $prefix);

        return $model->reference;
    }
}
