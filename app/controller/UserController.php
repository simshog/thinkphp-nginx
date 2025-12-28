<?php
namespace app\controller;

use think\facade\Request;
use think\facade\Db;
use app\model\User;

class UserController
{
    public function index()
    {
        $page = Request::param('page', 1);
        $limit = Request::param('limit', 10);
        $keyword = Request::param('keyword', '');
        $role = Request::param('role', '');
        $status = Request::param('status', '');
        
        $query = User::order('id', 'desc');
        
        if (!empty($keyword)) {
            $query->whereLike('nickname|phone', '%' . $keyword . '%');
        }
        
        if ($role !== '') {
            $query->where('role', $role);
        }
        
        if ($status !== '') {
            $query->where('status', $status);
        }
        
        $list = $query->page($page, $limit)->select();
        $total = $query->count();
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    public function read()
    {
        $id = Request::param('id');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '用户ID不能为空']);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $user
        ]);
    }
    
    public function save()
    {
        $data = Request::post();
        
        if (empty($data['openid'])) {
            return json(['code' => 1, 'msg' => 'openid不能为空']);
        }
        
        $user = User::where('openid', $data['openid'])->find();
        
        if ($user) {
            $user->save($data);
        } else {
            $user = User::create($data);
        }
        
        return json([
            'code' => 0,
            'msg' => '保存成功',
            'data' => $user
        ]);
    }
    
    public function update()
    {
        $id = Request::param('id');
        $data = Request::post();
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '用户ID不能为空']);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->save($data);
        
        return json([
            'code' => 0,
            'msg' => '更新成功',
            'data' => $user
        ]);
    }
    
    public function delete()
    {
        $id = Request::param('id');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '用户ID不能为空']);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->delete();
        
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
    
    public function updateStatus()
    {
        $id = Request::param('id');
        $status = Request::param('status');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '用户ID不能为空']);
        }
        
        if ($status === null || $status === '') {
            return json(['code' => 1, 'msg' => '状态不能为空']);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->status = $status;
        $user->save();
        
        return json([
            'code' => 0,
            'msg' => '状态更新成功',
            'data' => $user
        ]);
    }
    
    public function updateRole()
    {
        $id = Request::param('id');
        $role = Request::param('role');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '用户ID不能为空']);
        }
        
        if ($role === null || $role === '') {
            return json(['code' => 1, 'msg' => '角色不能为空']);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->role = $role;
        $user->save();
        
        return json([
            'code' => 0,
            'msg' => '角色更新成功',
            'data' => $user
        ]);
    }
    
    public function getByOpenid()
    {
        $openid = Request::param('openid');
        
        if (empty($openid)) {
            return json(['code' => 1, 'msg' => 'openid不能为空']);
        }
        
        $user = User::where('openid', $openid)->find();
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $user
        ]);
    }
    
    public function getChildren()
    {
        $parentId = Request::param('parent_id');
        
        if (empty($parentId)) {
            return json(['code' => 1, 'msg' => '上级ID不能为空']);
        }
        
        $children = User::where('parent_id', $parentId)->select();
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $children
        ]);
    }
    
    public function getByShop()
    {
        $shopId = Request::param('shop_id');
        
        if (empty($shopId)) {
            return json(['code' => 1, 'msg' => '店铺ID不能为空']);
        }
        
        $users = User::where('shop_id', $shopId)->select();
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $users
        ]);
    }
    
    public function getRoleList()
    {
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => User::getRoleList()
        ]);
    }
    
    public function getCurrentUser()
    {
        $openid = Request::header('x-wx-openid');
        
        if (empty($openid)) {
            return json(['code' => 1, 'msg' => 'openid不能为空']);
        }
        
        $user = User::where('openid', $openid)->find();
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $user
        ]);
    }
    
    public function getOpenidByCode()
    {
        $code = Request::param('code');
        
        if (empty($code)) {
            return json(['code' => 1, 'msg' => 'code不能为空']);
        }
        
        $appId = getenv('WECHAT_APPID');
        $appSecret = getenv('WECHAT_APPSECRET');
        
        if (empty($appId) || empty($appSecret)) {
            return json(['code' => 1, 'msg' => '微信配置不完整']);
        }
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
        
        $response = file_get_contents($url);
        $result = json_decode($response, true);
        
        if (isset($result['errcode'])) {
            return json(['code' => 1, 'msg' => $result['errmsg']]);
        }
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'openid' => $result['openid'],
                'session_key' => $result['session_key'] ?? ''
            ]
        ]);
    }
}
