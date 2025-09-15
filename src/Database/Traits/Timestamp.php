<?php
namespace Craft\Database\Traits;

/**
 * #### Timestamp trait
 * 
 * This trait provides functionality to manage created_at and updated_at timestamps for database models.
 */
trait Timestamp
{
    /**
     * Set the created_at timestamp to the current time.
     *
     * @return void
     */
    public function setCreatedAt()
    {
        if ($this->shouldTimestamp()) {
            $this->created_at = date('Y-m-d H:i:s');
        }
    }

    /**
     * Set the updated_at timestamp to the current time.
     *
     */
    public function setUpdatedAt()
    {
        if ($this->shouldTimestamp()) {
            $this->updated_at = date('Y-m-d H:i:s');
        }
    }

    /**
     * Touch the timestamps for created_at and updated_at.
     *
     * This method sets created_at if it is not already set, and updates updated_at to the current time.
     *
     * @return void
     */
    public function touchTimestamps()
    {
        if ($this->shouldTimestamp()) {
            $now = date('Y-m-d H:i:s');
            if (empty($this->created_at)) {
                $this->created_at = $now;
            }
            $this->updated_at = $now;
        }
    }

    /**
     * Save the model with updated timestamps.
     *
     * This method updates the created_at and updated_at fields before saving.
     *
     */
    public function saveWithTimestamps()
    {
        $this->touchTimestamps();
        return $this->save();
    }

    protected function shouldTimestamp()
    {
        return property_exists($this, 'timestamps') ? $this->timestamps : true;
    }
}