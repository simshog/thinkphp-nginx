<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'users';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = 'datetime';
    
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    protected $type = [
        'role' => 'integer',
        'parent_id' => 'integer',
        'shop_id' => 'integer',
        'status' => 'integer',
    ];
    
    const ROLE_USER = 1;
    const ROLE_SHOP_MANAGER = 2;
    const ROLE_TEAM_MEMBER = 3;
    const ROLE_OPERATION_ADMIN = 4;
    const ROLE_SUPER_ADMIN = 5;
    
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    
    public static function getRoleList()
    {
        return [
            self::ROLE_USER => '普通用户',
            self::ROLE_SHOP_MANAGER => '店铺管理',
            self::ROLE_TEAM_MEMBER => '团队成员',
            self::ROLE_OPERATION_ADMIN => '运营管理员',
            self::ROLE_SUPER_ADMIN => '超级管理员',
        ];
    }
    
    public function getRoleTextAttr($value, $data)
    {
        $roleList = self::getRoleList();
        return isset($roleList[$data['role']]) ? $roleList[$data['role']] : '';
    }
    
    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == self::STATUS_ENABLED ? '启用' : '禁用';
    }
    
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }
    
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id', 'id');
    }
    
    public function shop()
    {
        return $this->belongsTo('app\model\Shop', 'shop_id', 'id');
    }
}
