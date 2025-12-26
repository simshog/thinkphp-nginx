<?php
namespace app\model;

use think\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
}