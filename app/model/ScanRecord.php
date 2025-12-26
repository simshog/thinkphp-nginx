<?php
namespace app\model;

use think\Model;

class ScanRecord extends Model
{
    protected $table = 'scan_records';
    protected $autoWriteTimestamp = false;
    
    public function wifiInfo()
    {
        return $this->belongsTo(WifiInfo::class, 'wifi_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}