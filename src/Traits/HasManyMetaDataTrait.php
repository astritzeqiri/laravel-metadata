<?php 

namespace AstritZeqiri\Metadata\Traits;

use AstritZeqiri\Metadata\Models\MetaData;

/**
 * This is the has many media trait.
 *
 */
trait HasManyMetaDataTrait
{
    /**
     * This method runs when the trait has booted
     * 
     */
    public static function bootHasManyMetaDataTrait()
    {
        $name = static::class;
        // Add a listener on deleting a object and delete all of its metas
        \Event::listen("eloquent.deleting: {$name}", function ($model) {
            $model->delete_all_metas();
        });
    }
    /**
     * Get the meta_data relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function meta_data()
    {
        return $this->morphMany(MetaData::class, 'object');
    }
    /**
     * Update the meta if it exists else create a new one
     * 
     * @param 
     * 
     * @return Item App\MetaData
     */
    public function update_meta($key = null, $value = null)
    {
        if ($key == null || $value == null) {
            return false;
        }

        $exists_meta = $this->get_meta($key);
        
        if ($exists_meta == null) {
            // create a new meta_data
            $meta = $this->meta_data()->create([
                'key' => $key,
                'value' => $value
            ]);
            return $meta;
        } else {
            // update the existing meta_data
            $exists_meta->update(['value' => $value]);
            return $exists_meta;
        }
    }

    /**
     * Get a meta data
     * 
     * @param string $key the meta key 
     * @param boolean $get_value (if true return only the data else return the object)
     * 
     * @return \App\Media or string  (if $get_value is true return only the data else return the object)
     */
    public function get_meta($key = null, $get_value = false)
    {
        if (! $key) {
            return null;
        }

        $meta = $this->meta_data()->whereKey($key)->first();

        if (! $meta) {
            return null;
        }

        return $get_value == true ? $meta->value : $meta;
    }
    
    /**
     * Delete all media.
     *
     * @param string $key the meta key 
     * 
     * @return boolean (if the meta was deleted)
     */
    public function delete_meta($key = null)
    {
        if (! $key) {
            return false;
        }

        $meta = $this->get_meta($key);
        
        if (! $meta) {
            return false;
        }

        return $meta->delete();
    }
    
    /**
     * Delete all the meta data of the object.
     *
     * @return boolean (if the meta was deleted)
     */
    public function delete_all_metas()
    {
        $this->meta_data->each(function ($model) { $model->delete(); });
    }

    /**
     *  Filter items by a given array of meta_datas
     * 
     * @param $query
     * @param array $values [array of metadatas (Item example ["item" => "", "value" => "", "compare" => ""])]
     * @param string $relation [how you want to search with AND or OR]
     * 
     * @return $query
     */
    public function scopeMetaQuery($query, $values = array(), $relation = "AND")
    {
        if (! is_array($values)) {
            return $query;
        }

        $values = $this->filterMetaQueryArray($values);
        
        if (empty($values)) {
            return $query;
        }

        $relation = $this->resolveRelation($relation);
        
        if (count($values) == 1) {
            $relation = "AND";
        }

        return $query->where(function ($query) use ($values, $relation) {
            $method = $this->getMethodFromRelation($relation);

            foreach ($values as $value) {
                $query->$method(
                    'meta_data',
                    $this->createQueryForMeta(
                        $value['key'],
                        $value['value'],
                        $value['compare']
                    )
                );
            }
        });
    }

    /**
     * Return only one meta query filter.
     * 
     * @param $query
     * @param string $key the meta key
     * @param string $value the meta value
     * @param string $compare the meta compare
     * 
     * @return $query
     */
    private function createQueryForMeta($key, $value, $compare = "=")
    {
        return function ($query) use ($key, $value, $compare) {
            $query->where('key', $key)->where('value', $compare, $value);
        };
    }

    /**
     * Filter the meta query array values.
     * 
     * @param  array $items
     * 
     * @return array
     */
    private function filterMetaQueryArray($items = [])
    {
        return collect($items)
        ->map(function ($item) {
            return $this->makeMetaQueryArrayItem($item);
        })
        ->filter()
        ->values()
        ->toArray();
    }

    /**
     * Make a metaquery array item (Item example ["item" => "", "value" => "", "compare" => ""])]
     * 
     * @param  array  $item
     * 
     * @return array|null 
     */
    private function makeMetaQueryArrayItem($item = [])
    {
        if (! is_array($item)) {
            return null;
        }
        
        if (! array_key_exists("key", $item) || ! array_key_exists("value", $item)) {
            return null;
        }

        if (! array_key_exists("compare", $item)) {
            $item['compare'] = "=";
        }

        return $item;
    }

    /**
     * Get the method from the relation.
     * 
     * @param  string $relation
     * 
     * @return string
     */
    private function getMethodFromRelation($relation = 'AND')
    {
        if ($relation == "OR") {
            return 'orWhereHas';
        }

        $method = 'whereHas';
    }

    /**
     * Resolve the relation.
     * 
     * @param  string $relation
     * 
     * @return string $relation
     */
    private function resolveRelation($relation)
    {
        $relations = array("OR", "AND");
        
        if (! in_array($relation, $relations)) {
            $relation = "AND";
        }

        return $relation;
    }
}
