<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $table = 'users';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 角色常量
    const ROLE_USER = 1;        // 普通用户
    const ROLE_SHOP = 2;        // 店铺管理
    const ROLE_TEAM = 3;        // 团队成员
    const ROLE_AGENT = 4;       // 市代理
    const ROLE_ADMIN = 5;       // 总管理员
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
    public function getRoleTextAttr($value, $data)
    {
        $roles = [
            self::ROLE_USER => '普通用户',
            self::ROLE_SHOP => '店铺管理',
            self::ROLE_TEAM => '团队成员',
            self::ROLE_AGENT => '市代理',
            self::ROLE_ADMIN => '总管理员'
        ];
        return $roles[$data['role']] ?? '未知';
    }
    
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用'
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }
    
    public function wifiInfos()
    {
        return $this->hasMany(WifiInfo::class, 'created_by');
    }
}