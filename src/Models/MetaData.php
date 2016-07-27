<?php

namespace AstritZeqiri\Metadata\Models;

use Illuminate\Database\Eloquent\Model;
use AstritZeqiri\Metadata\Traits\HasRelatedObjectTrait;

class MetaData extends Model
{
  use HasRelatedObjectTrait;

  protected $guarded = array();
  
  /**
   * The attribute that shows if the table has timestamps
   * 
   * @var boolean
   */
  public $timestamps = false;

  /**
   * The table name 
   * 
   * @var string
   */
  protected $table = 'meta_datas';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = array(
    'key',
    'value',
    'object_id',
    'object_type',
  );
}
