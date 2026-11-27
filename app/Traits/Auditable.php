<?php

namespace App\Traits;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditLogger::log('model.created', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            if ($dirty === []) {
                return;
            }
            $old = array_intersect_key($model->getOriginal(), $dirty);
            AuditLogger::log('model.updated', $model, $old, $dirty);
        });

        static::deleted(function (Model $model) {
            AuditLogger::log('model.deleted', $model, $model->getOriginal(), null);
        });
    }
}
