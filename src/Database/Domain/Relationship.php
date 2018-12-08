<?php

namespace Fenix\Database\Domain;

use Fenix\Database\Collection\Collection;

class Relationship
{

    protected $exclude = [];

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

        $hasMany = $hasMany->select('*')->where($foreignKey, $this->{$foreignKey})->get();

        $this->{$property} = $hasMany;

        $this->exclude[] = $property;

        return $this;
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

        $belongsTo = $belongsTo->select('*')
                                ->where($foreignKey, $this->{$foreignKey})
                                ->get();

        $this->{$property} = $belongsTo;

        $this->exclude[] = $property;


        return $this;
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

        $hasOne = $hasOne->select('*')
                            ->where($foreignKey, $this->{$foreignKey})
                            ->get();

        $this->{$property} = $hasOne instanceof Collection ? $hasOne->first() : $hasOne;

        $this->exclude[] = $property;

        return $this;

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