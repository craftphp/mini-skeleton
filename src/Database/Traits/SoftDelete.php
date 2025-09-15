<?php
namespace Craft\Database\Traits;

/**
 * #### SoftDelete trait
 *
 * This trait provides functionality to mark an object as deleted without actually removing it from the database.
 * 
 * It allows for soft deletion and restoration of the object.
 */
trait SoftDelete
{
    /**
     * The timestamp when the object was soft deleted
     *
     * @var string|null
     */
    public function softDelete()
    {
        if ($this->isDeleted()) {
            return false;
        }
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Restore the object from soft deletion
     *
     * @return bool
     */
    public function restore($id = null)
    {
        if (!$this->isDeleted()) {
            return false;
        }
        $this->deleted_at = null;
        return $this->save();
    }
    /**
     * Check if the object is soft deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return !is_null($this->deleted_at);
    }
}