<?php 

namespace AstritZeqiri\Metadata\Traits;

use AstritZeqiri\Metadata\Models\MetaData;
use AstritZeqiri\Metadata\Observers\ModelObserver;

/**
 * This is the has many media trait.
 *
 */
trait HasManyMetaDataTrait
{
    /**
     * This method runs when the trait has booted.
     */
    public static function bootHasManyMetaDataTrait()
    {
        static::observe(ModelObserver::class);
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
     * @param string $key
     * @param string|null|boolean $value
     * 
     * @return AstritZeqiri\Metadata\Models\MetaData|null
     */
    public function update_meta($key = null, $value = null)
    {
        if (is_array($key)) {
            return $this->update_meta_array($key);
        }

        if (!is_string($key)) {
            return null;
        }

        $meta = $this->get_meta($key);
        
        if (!$meta) {
            return $this->meta_data()->create(compact(
                'key', 'value'
            ));
        }

        $meta->update(['value' => $value]);
        
        return $meta;
    }

    /**
     * Update meta data from a given array.
     * 
     * @param  array  $metas
     * 
     * @return Collection of MetaData objects.
     */
    public function update_meta_array(array $metas = [])
    {
        return collect($metas)
        ->map(function($value, $key) {
            return $this->update_meta($key, $value);
        })
        ->filter()
        ->values();
    }

    /**
     * Get a meta data.
     * 
     * @param string $key the meta key 
     * @param boolean $onlyValue (if true return only the value else return the object)
     * 
     * @return AstritZeqiri\Metadata\Models\MetaData|string
     */
    public function get_meta($key = null, $onlyValue = false)
    {
        if (!is_string($key)) {
            return null;
        }

        $meta = $this->meta_data()->where('key', $key)->first();

        if (!$meta) {
            return null;
        }

        return $onlyValue == true ? $meta->value : $meta;
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
        if (! is_string($key)) {
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
        return $this->meta_data()->delete();
    }

    /**
     *  Filter items by a given array of meta_datas.
     * 
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $values [array of metadatas (Item example ["item" => "", "value" => "", "compare" => ""])]
     * @param string $relation [how you want to search with AND or OR]
     * 
     * @return Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeMetaQuery($query, $values, $value = '', $relation = "AND")
    {
        // Check if the values argument is a string and the make a new meta query 
        // where values is the key, value is the value and relation is the comparator.
        if (is_string($values)) {
            list($key, $value, $compare) = [$values, $value, $relation];
            
            return $query->metaQuery([compact('key', 'value', 'compare')]);
        }

        // If the values is not an array something is wrong with the query
        // arguments and we just return the query as it was given.
        if (!is_array($values)) {
            return $query;
        }

        // If the {$values} is an array the the relation is the second argument {$value} argument.
        $relation = $value;

        // Filter the given meta query array
        $values = $this->filterMetaQueryArray($values);

        // If the filtered array contains no elements
        // then return the given query
        if (empty($values)) {
            return $query;
        }

        // Resolve the query relation 'OR' or 'AND'.
        // if there is only one item in the values the relation is 'OR'
        // else we call the resolveRelation method
        $relation = count($values) == 1 ? "AND" : $this->resolveRelation($relation);

        // Make the meta query
        return $query->where(function($query) use ($values, $relation) {
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
     * @param  string $key the meta key
     * @param  string $value the meta value
     * @param  string $compare the meta compare
     * 
     * @return Illuminate\Database\Eloquent\Builder $query
     */
    private function createQueryForMeta($key, $value, $compare = "=")
    {
        return function($query) use ($key, $value, $compare) {
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
        ->map(function($item) {
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
        if (!is_array($item)) {
            return null;
        }
        
        if (!array_key_exists("key", $item) || !array_key_exists("value", $item)) {
            return null;
        }

        if (!array_key_exists("compare", $item)) {
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

        return 'whereHas';
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
        
        if (!in_array($relation, $relations)) {
            $relation = "AND";
        }

        return $relation;
    }
}
