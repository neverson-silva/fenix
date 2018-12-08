<?php

namespace Fenix\Traits\Support\Collection;

/**
 * Common to structures that deal with an internal capacity. While none of the
 * PHP implementations actually make use of a capacity, it's important to keep
 * consistent with the extension.
 *
 * @see php-ds/polyfill
 * @see https://github.com/php-ds/polyfill/blob/master/src/Traits/Capacity.php
 */

trait Capacity
{


    /**
     * @var integer internal capacity
     */
    protected $capacity = self::MIN_CAPACITY;

    /**
     * Ensures that enough memory is allocated for a required capacity. This removes
     * the need to reallocate the internal as values are added
     * @param int $capacity
     * @return void
     */
    public function allocate(int $capacity): void
    {
        $this->capacity = max($capacity, $this->capacity);
    }

    /**
     * Returns the current capacity.
     * @return int
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * @return the structures growth factor.
     */
    protected function getGrowthFactor(): float
    {
        return 2;
    }
    /**
     * @return float to multiply by when decreasing capacity.
     */
    protected function getDecayFactor(): float
    {
        return 0.5;
    }

    /**
     * the ratio between size and capacity when capacity should be
     *               decreased.
     * @return float
     */
    protected function getTruncateThreshold(): float
    {
        return 0.25;
    }
    /**
     * Checks and adjusts capacity if required.
     */
    protected function checkCapacity()
    {
        if ($this->shouldIncreaseCapacity()) {
            $this->increaseCapacity();
        } else {
            if ($this->shouldDecreaseCapacity()) {
                $this->decreaseCapacity();
            }
        }
    }
    /**
     * Called when capacity should be increased to accommodate new values.
     */
    protected function increaseCapacity()
    {
        $this->capacity = max($this->count(), $this->capacity * $this->getGrowthFactor());
    }
    /**
     * Called when capacity should be decrease if it drops below a threshold.
     */
    protected function decreaseCapacity()
    {
        $this->capacity = max(self::MIN_CAPACITY, $this->capacity  * $this->getDecayFactor());
    }
    /**
     * whether capacity should be increased.
     * @return bool
     */
    protected function shouldDecreaseCapacity(): bool
    {
        return count($this) <= $this->capacity * $this->getTruncateThreshold();
    }

    /**
     * whether capacity should be increased.
     * @return bool
     */
    protected function shouldIncreaseCapacity(): bool
    {
        return $this->count() >= $this->capacity;
    }
}