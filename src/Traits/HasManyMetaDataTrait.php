<?php 

namespace AstritZeqiri\Metadata\Traits;
use AstritZeqiri\Metadata\Models\Metadata as Metadata;
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
    public static function bootHasManyMetaDataTrait(){
        $name = static::class;
        // Add a listener on deleting a object and delete all of its metas
        \Event::listen("eloquent.deleting: {$name}", function($model){
            $model->delete_all_metas();
        });
    }
    /**
     * Get the meta_data relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneOrMany
     */
    public function meta_data()
    {
        return $this->hasMany('AstritZeqiri\Metadata\Models\Metadata', 'object_id', 'id')->where('object_type', self::class);
    }
    /**
     * Update the meta if it exists else create a new one
     * 
     * @param 
     * 
     * @return Item Metadata
     */
    public function update_meta($key = null, $value = null)
    {
        if($key == null || $value == null) return false;

        $exists_meta = $this->get_meta($key);
        
        if($exists_meta == null){
            // create a new meta_data
            $meta = new Metadata;
            $meta->key = $key;
            $meta->value = $value;
            $meta->regarding($this);
            $meta->save();
            return $meta;
        }else{
            // update the existing meta_data
            $exists_meta->value = $value;
            $exists_meta->save();
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
        if($key == null || ($meta = $this->meta_data()->whereKey($key)->first()) == null)
            return $get_value == true ? "" : null;

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
        if($key == null) return false;
        $meta = $this->get_meta($key);
        if($meta != null){
            return $meta->delete();
        }
        return false;
    }
    
    /**
     * Delete all the meta data of the object.
     *
     * @return boolean (if the meta was deleted)
     */
    public function delete_all_metas()
    {
        $this->meta_data->each(function($model){ $model->delete(); });
    }

    /**
     *  return only one meta query filter
     * 
     * @param $query
     * @param string $key the meta key
     * @param string $value the meta value
     * @param string $compare the meta compare
     * 
     * @return $query
     */
    public function createQueryForMeta($key = null, $value = null, $compare = "="){
        return function($query) use($key, $value, $compare){
            $query->where('key', $key)->where('value', $compare, $value);
        };
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
    public function scopeMetaQuery($query, $values = array(), $relation = "AND"){
        if(!is_array($values)) return $query;

        $relations = array("OR", "AND");
        
        if(!in_array($relation, $relations)) $relation = "AND";

        $values = $this->filterMetaQueryArray($values);
        
        if(empty($values)) return $query;
        
        if(count($values) == 1){
            $relation = "AND";
        }

        return $query->where(function($query) use($values, $relation){
            foreach($values as $value){
                if($relation == "OR"){
                    $query->orWhereHas('meta_data', $this->createQueryForMeta($value['key'], $value['value'], $value['compare']));
                }else if($relation == "AND"){
                    $query->whereHas('meta_data', $this->createQueryForMeta($value['key'], $value['value'], $value['compare']));
                }
            }
        });
    }

    /**
     * Filter the meta query array values
     * @param  array  $array [description]
     * @return [type]        [description]
     */
    public function filterMetaQueryArray($array = array()){
        return array_values(array_filter(array_map(function($item){
            return $this->makeMetaQueryArrayItem($item);
        }, $array)));
    }

    /**
     * Make a metaquery array item (Item example ["item" => "", "value" => "", "compare" => ""])]
     * 
     * @param  array  $item [description]
     * 
     * @return array or false (return the array of return false) 
     */
    public function makeMetaQueryArrayItem($item = array()){
        if($item == null || !is_array($item) || empty($item)) return false;
        
        if(!isset($item['key']) || !isset($item['value'])) return false;

        if(!isset($item['compare'])) $item['compare'] = "=";

        return $item;
    }
}