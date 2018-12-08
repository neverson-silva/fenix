<?php

namespace Fenix\Orm;

use Fenix\Orm\Collection\Collection;

class Relationship
{
    protected $loadRelations = false;

    /**
     * @var array|string
     */
    protected $hasMany = [];

    /**
     * @var array|string
     */
    protected $belongsTo = [];

    /**
     * @var array
     */
    protected $hasOne = [];

    /**
     * Has Many
     */
    public function hasMany($hasMany, $foreignKey = null, $primaryKey = null)
    {
        $hasMany = new $hasMany();

        $property = $hasMany->getTable();

        $foreignKey = $foreignKey ?? $this->getPrimaryKey();

        $primaryKey = $primaryKey ?? $this->getPrimaryKey();

        $hasMany = $hasMany->where($foreignKey, $this->{$foreignKey})->get();

        return $this->setRelation($property, $hasMany);
    }

    /**
     * Try to match the foreign key here to the Model that owns it, can be passed a foreign key
     */
    public function belongsTo($belongsTo, $foreignKey = null, $primaryKey = null)
    {
        $property = explode('\\', $belongsTo);

        $property = strtolower(array_pop($property));

        $belongsTo = new $belongsTo();

        $foreignKey = $foreignKey ?? $belongsTo->getPrimaryKey();

        $primaryKey = $primaryKey ?? $this->getPrimaryKey();

        $belongsTo = $belongsTo->where($foreignKey, $this->{$foreignKey})
                               ->get();

        return $this->setRelation($property, $belongsTo);
    }

    /**
     * Has one
     */
    public function hasOne($hasOne, $foreignKey = null, $primaryKey = null)
    {

        $property = explode('\\', $hasOne);

        $property = strtolower(array_pop($property));

        $hasOne = new $hasOne();

        $foreignKey = $foreignKey ?? $hasOne->getPrimaryKey();

        $primaryKey = $primaryKey ?? $this->getPrimaryKey();

        if (isset($this->{$property})) {
            return $this;
        }

        $hasOne = $hasOne->where($foreignKey, $this->{$foreignKey})
                        ->get();

        return $this->setRelation($property, $hasOne instanceof Collection ? $hasOne->first() : $hasOne);
    }

    /**
     * Load all relations
     * @return $this
     */
    public function loadAllRelations()
    {
        if (!is_null($this->belongsTo)) {
            $this->loadBelongsTo();
        }
        if (!is_null($this->hasMany)) {
            $this->loadHasMany();
        }

        if (!is_null($this->hasOne)) {
            $this->loadHasOne();
        }

        return $this;
    }

    /**
     * Load all belongsTo/HasOne
     */
    public function loadBelongsTo()
    {
        if (is_array($this->belongsTo)) {
            foreach ($this->belongsTo as $belongsTo) {
                if (is_array($belongsTo)) {
                    $this->belongsTo(...$belongsTo);
                } else {
                    $this->belongsTo($belongsTo);
                }
            }
        } else {
            $this->belongsTo($this->belongsTo);
        }
    }

    /**
     * Load the related
     */
    public function loadHasMany()
    {
        if (is_array($this->hasMany)) {
            foreach ($this->hasMany as $hasMany) {
                if (is_array($hasMany)) {
                    $this->hasMany(...$hasMany);
                } else {
                    $this->hasMany($hasMany);
                }
            }
        } else {
            $this->hasMany($this->hasMany);
        }
    }

    public function loadHasOne()
    {
        if (is_array($this->hasOne)) {
            foreach ($this->hasOne as $hasOne) {
                if (is_array($hasOne)) {
                    $this->hasOne(...$hasOne);
                } else {
                    $this->hasOne($hasOne);
                }
            }
        } else {
            $this->hasOne($this->hasOne);
        }
    }
}