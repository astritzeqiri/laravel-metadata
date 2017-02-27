<?php

namespace AstritZeqiri\Metadata\Observers;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Listen to the Model deleting event and delete their meta_data.
     *
     * @param  Model  $model
     * @return void
     */
    public function deleting(Model $model)
    {
        $model->delete_all_metas();
    }
}
