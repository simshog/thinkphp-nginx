<?php
namespace app\controller;

use app\model\WifiInfo;
use app\model\Shop;
use app\model\ScanRecord;

class WifiController extends BaseController
{
    public function list()
    {
        if (!$this->checkPermission('wifi_manage')) {
            return $this->error('权限不足');
        }
        
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $shopId = $this->request->param('shop_id');
        $keyword = $this->request->param('keyword');
        
        $query = WifiInfo::with(['shop', 'creator']);
        
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        
        if ($keyword) {
            $query->where('ssid', 'like', "%{$keyword}%");
        }
        
        if ($this->user->role == app\model\User::ROLE_SHOP) {
            $query->where('shop_id', $this->user->shop_id);
        } elseif ($this->user->role == app\model\User::ROLE_TEAM) {
            $shopIds = Shop::where('city_agent_id', $this->user->parent_id)->column('id');
            $query->whereIn('shop_id', $shopIds);
        } elseif ($this->user->role == app\model\User::ROLE_AGENT) {
            $shopIds = Shop::where('city_agent_id', $this->userId)->column('id');
            $query->whereIn('shop_id', $shopIds);
        }
        
        $list = $query->paginate(['page' => $page, 'list_rows' => $limit]);
        
        return $this->success($list);
    }
    
    public function create()
    {
        if (!$this->checkPermission('wifi_manage')) {
            return $this->error('权限不足');
        }
        
        $data = $this->request->only(['shop_id', 'ssid', 'password', 'encryption_type', 'is_hidden']);
        $data['created_by'] = $this->userId;
        
        if ($this->user->role == app\model\User::ROLE_SHOP) {
            $data['shop_id'] = $this->user->shop_id;
        }
        
        $wifi = new WifiInfo();
        $wifi->save($data);
        
        $qrCodeUrl = $this->generateQrCode($wifi->id);
        if ($qrCodeUrl) {
            $wifi->qr_code_url = $qrCodeUrl;
            $wifi->save();
        }
        
        return $this->success(['id' => $wifi->id, 'qr_code_url' => $qrCodeUrl], '创建成功');
    }
    
    public function update($id)
    {
        if (!$this->checkPermission('wifi_manage')) {
            return $this->error('权限不足');
        }
        
        $wifi = WifiInfo::find($id);
        if (!$wifi) {
            return $this->error('WIFI信息不存在');
        }
        
        if (!$this->checkWifiPermission($wifi)) {
            return $this->error('权限不足');
        }
        
        $data = $this->request->only(['ssid', 'password', 'encryption_type', 'is_hidden', 'status']);
        $wifi->save($data);
        
        return $this->success(null, '更新成功');
    }
    
    public function delete($id)
    {
        if (!$this->checkPermission('wifi_manage')) {
            return $this->error('权限不足');
        }
        
        $wifi = WifiInfo::find($id);
        if (!$wifi) {
            return $this->error('WIFI信息不存在');
        }
        
        if (!$this->checkWifiPermission($wifi)) {
            return $this->error('权限不足');
        }
        
        $wifi->delete();
        
        return $this->success(null, '删除成功');
    }
    
    public function detail($id)
    {
        $wifi = WifiInfo::with(['shop', 'creator', 'scanRecords'])->find($id);
        if (!$wifi) {
            return $this->error('WIFI信息不存在');
        }
        
        if (!$this->checkWifiPermission($wifi)) {
            return $this->error('权限不足');
        }
        
        return $this->success($wifi);
    }
    
    public function connect($id)
    {
        $wifi = WifiInfo::find($id);
        if (!$wifi || $wifi->status != WifiInfo::STATUS_ENABLED) {
            return $this->error('WIFI信息不存在或已禁用');
        }
        
        $record = new ScanRecord();
        $record->wifi_id = $id;
        $record->user_id = $this->userId;
        $record->ip_address = $this->request->ip();
        $record->user_agent = $this->request->header('user-agent');
        $record->save();
        
        $wifi->scan_count += 1;
        $wifi->save();
        
        return $this->success([
            'ssid' => $wifi->ssid,
            'password' => $wifi->password,
            'encryption_type' => $wifi->encryption_type,
            'is_hidden' => $wifi->is_hidden
        ]);
    }
    
    public function qrcode($id)
    {
        $wifi = WifiInfo::find($id);
        if (!$wifi) {
            return $this->error('WIFI信息不存在');
        }
        
        if (!$this->checkWifiPermission($wifi)) {
            return $this->error('权限不足');
        }
        
        if (!$wifi->qr_code_url) {
            $qrCodeUrl = $this->generateQrCode($wifi->id);
            if ($qrCodeUrl) {
                $wifi->qr_code_url = $qrCodeUrl;
                $wifi->save();
            }
        }
        
        return $this->success(['qr_code_url' => $wifi->qr_code_url]);
    }
    
    private function checkWifiPermission($wifi)
    {
        if ($this->user->role == app\model\User::ROLE_ADMIN) {
            return true;
        }
        
        if ($this->user->role == app\model\User::ROLE_AGENT) {
            $shop = Shop::find($wifi->shop_id);
            return $shop && $shop->city_agent_id == $this->userId;
        }
        
        if ($this->user->role == app\model\User::ROLE_TEAM) {
            $shop = Shop::find($wifi->shop_id);
            return $shop && $shop->city_agent_id == $this->user->parent_id;
        }
        
        if ($this->user->role == app\model\User::ROLE_SHOP) {
            return $wifi->shop_id == $this->user->shop_id;
        }
        
        return false;
    }
}