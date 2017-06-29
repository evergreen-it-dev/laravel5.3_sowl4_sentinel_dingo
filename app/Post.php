<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    private function getParentNameRecursively($parent_id)
    {
        $parent = self::find($parent_id);
        $parent_name = '';

        if($parent && $parent->parent_id > 0){
            $parent_name .= $this->getParentNameRecursively($parent->parent_id) . ' > ';
        }
        return $parent_name . $parent->name;
    }

    public function getHierarchicalNameAttribute()
    {
        $hierarchical_name = '';

        if($this->parent_id > 0){
            $hierarchical_name .= $this->getParentNameRecursively($this->parent_id) . ' > ' ;
        }
        return $hierarchical_name .$this->name;
    }
}
