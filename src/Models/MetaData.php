<?php

namespace AstritZeqiri\Metadata\Models;

use AstritZeqiri\Metadata\Traits\HasRelatedObjectTrait;
use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{
    use HasRelatedObjectTrait;

    protected $guarded = [];

  /**
   * The attribute that shows if the table has timestamps.
   *
   * @var bool
   */
  public $timestamps = false;

  /**
   * The table name.
   *
   * @var string
   */
  protected $table = 'meta_datas';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'key',
    'value',
    'object_id',
    'object_type',
  ];
}
