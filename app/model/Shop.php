<?php
namespace app\model;

use think\Model;

class Shop extends Model
{
    protected $table = 'shops';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用'
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    public function cityAgent()
    {
        return $this->belongsTo(User::class, 'city_agent_id');
    }
    
    public function wifiInfos()
    {
        return $this->hasMany(WifiInfo::class, 'shop_id');
    }
}