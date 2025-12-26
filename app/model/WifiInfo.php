<?php
namespace app\model;

use think\Model;

class WifiInfo extends Model
{
    protected $table = 'wifi_info';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 加密类型常量
    const ENCRYPTION_WPA = 'WPA';
    const ENCRYPTION_WEP = 'WEP';
    const ENCRYPTION_NONE = 'None';
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
    public function getEncryptionTextAttr($value, $data)
    {
        $encryption = [
            self::ENCRYPTION_WPA => 'WPA/WPA2',
            self::ENCRYPTION_WEP => 'WEP',
            self::ENCRYPTION_NONE => '无加密'
        ];
        return $encryption[$data['encryption_type']] ?? '未知';
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
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function scanRecords()
    {
        return $this->hasMany(ScanRecord::class, 'wifi_id');
    }
}