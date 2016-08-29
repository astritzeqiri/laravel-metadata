<?php

namespace AstritZeqiri\Metadata\Traits;

/**
 * This is the has related object trait.
 */
trait HasRelatedObjectTrait
{
    /**
     * set the object that the media belongs to.
     *
     * @param  $object the instance
     *
     * @return $this
     */
    public function regarding($object)
    {
        if (is_object($object)) {
            $this->object_id = $object->id;
            $this->object_type = get_class($object);
        }

        return $this;
    }

    /**
     * check if the notification has a valid related object.
     *
     * @return bool
     */
    public function hasValidObject()
    {
        try {
            $object = call_user_func_array($this->object_type.'::findOrFail', [$this->object_id]);
        } catch (\Exception $e) {
            return false;
        }
        $this->relatedObject = $object;

        return true;
    }

    /**
     * get the related object.
     *
     * @return bool
     */
    public function getObject()
    {
        if (!$this->relatedObject) {
            $hasObject = $this->hasValidObject();
            if (!$hasObject) {
                return;
            }
        }

        return $this->relatedObject;
    }
}
