<?php
namespace app\controller;

use app\model\Shop;
use app\model\User;

class ShopController extends BaseController
{
    public function list()
    {
        if (!$this->checkPermission('shop_manage')) {
            return $this->error('权限不足');
        }
        
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $keyword = $this->request->param('keyword');
        
        $query = Shop::with(['manager', 'cityAgent']);
        
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }
        
        if ($this->user->role == User::ROLE_AGENT) {
            $query->where('city_agent_id', $this->userId);
        } elseif ($this->user->role == User::ROLE_TEAM) {
            $query->where('city_agent_id', $this->user->parent_id);
        }
        
        $list = $query->paginate(['page' => $page, 'list_rows' => $limit]);
        
        return $this->success($list);
    }
    
    public function create()
    {
        if (!$this->checkPermission('shop_manage')) {
            return $this->error('权限不足');
        }
        
        $data = $this->request->only(['name', 'address', 'contact_phone', 'manager_id']);
        
        if ($this->user->role == User::ROLE_AGENT) {
            $data['city_agent_id'] = $this->userId;
        } else {
            $data['city_agent_id'] = $this->request->param('city_agent_id');
        }
        
        $shop = new Shop();
        $shop->save($data);
        
        if ($data['manager_id']) {
            $manager = User::find($data['manager_id']);
            if ($manager) {
                $manager->shop_id = $shop->id;
                $manager->role = User::ROLE_SHOP;
                $manager->save();
            }
        }
        
        return $this->success($shop->id, '创建成功');
    }
    
    public function update($id)
    {
        if (!$this->checkPermission('shop_manage')) {
            return $this->error('权限不足');
        }
        
        $shop = Shop::find($id);
        if (!$shop) {
            return $this->error('店铺不存在');
        }
        
        if (!$this->checkShopPermission($shop)) {
            return $this->error('权限不足');
        }
        
        $data = $this->request->only(['name', 'address', 'contact_phone', 'manager_id', 'status']);
        
        if (isset($data['manager_id']) && $data['manager_id'] != $shop->manager_id) {
            if ($shop->manager_id) {
                $oldManager = User::find($shop->manager_id);
                if ($oldManager) {
                    $oldManager->shop_id = 0;
                    $oldManager->role = User::ROLE_USER;
                    $oldManager->save();
                }
            }
            
            $newManager = User::find($data['manager_id']);
            if ($newManager) {
                $newManager->shop_id = $shop->id;
                $newManager->role = User::ROLE_SHOP;
                $newManager->save();
            }
        }
        
        $shop->save($data);
        
        return $this->success(null, '更新成功');
    }
    
    public function delete($id)
    {
        if (!$this->checkPermission('shop_manage')) {
            return $this->error('权限不足');
        }
        
        $shop = Shop::find($id);
        if (!$shop) {
            return $this->error('店铺不存在');
        }
        
        if (!$this->checkShopPermission($shop)) {
            return $this->error('权限不足');
        }
        
        $shop->delete();
        
        return $this->success(null, '删除成功');
    }
    
    public function detail($id)
    {
        $shop = Shop::with(['manager', 'cityAgent', 'wifiInfos'])->find($id);
        if (!$shop) {
            return $this->error('店铺不存在');
        }
        
        if (!$this->checkShopPermission($shop)) {
            return $this->error('权限不足');
        }
        
        return $this->success($shop);
    }
    
    private function checkShopPermission($shop)
    {
        if ($this->user->role == User::ROLE_ADMIN) {
            return true;
        }
        
        if ($this->user->role == User::ROLE_AGENT) {
            return $shop->city_agent_id == $this->userId;
        }
        
        if ($this->user->role == User::ROLE_TEAM) {
            return $shop->city_agent_id == $this->user->parent_id;
        }
        
        return false;
    }
}