<?php
namespace app\controller;

use app\model\User;
use app\model\Shop;

class UserController extends BaseController
{
    public function login()
    {
        $code = $this->request->post('code');
        if (!$code) {
            return $this->error('缺少code参数');
        }
        
        $userInfo = $this->getWechatUserInfo($code);
        if (!$userInfo) {
            return $this->error('微信登录失败');
        }
        
        $openid = $userInfo['openid'];
        $user = User::where('openid', $openid)->find();
        
        if (!$user) {
            $user = new User();
            $user->openid = $openid;
            $user->unionid = $userInfo['unionid'] ?? '';
            $user->nickname = $userInfo['nickname'] ?? '';
            $user->avatar = $userInfo['headimgurl'] ?? '';
            $user->role = User::ROLE_USER;
            $user->save();
        }
        
        $token = $this->generateToken($user->id);
        
        return $this->success([
            'token' => $token,
            'user_info' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'role_text' => $user->role_text
            ]
        ]);
    }
    
    public function list()
    {
        if (!$this->checkPermission('user_manage')) {
            return $this->error('权限不足');
        }
        
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $role = $this->request->param('role');
        $keyword = $this->request->param('keyword');
        
        $query = User::with(['shop', 'parent']);
        
        if ($role) {
            $query->where('role', $role);
        }
        
        if ($keyword) {
            $query->where('nickname', 'like', "%{$keyword}%");
        }
        
        if ($this->user->role == User::ROLE_AGENT) {
            $query->where('parent_id', $this->userId);
        } elseif ($this->user->role == User::ROLE_TEAM) {
            $query->where('parent_id', $this->user->parent_id);
        }
        
        $list = $query->paginate(['page' => $page, 'list_rows' => $limit]);
        
        return $this->success($list);
    }
    
    public function create()
    {
        if (!$this->checkPermission('user_manage')) {
            return $this->error('权限不足');
        }
        
        $data = $this->request->only(['nickname', 'phone', 'role', 'shop_id', 'parent_id']);
        
        if ($this->user->role == User::ROLE_AGENT) {
            $data['parent_id'] = $this->userId;
        }
        
        $user = new User();
        $user->save($data);
        
        return $this->success($user->id, '创建成功');
    }
    
    public function update($id)
    {
        if (!$this->checkPermission('user_manage')) {
            return $this->error('权限不足');
        }
        
        $user = User::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $data = $this->request->only(['nickname', 'phone', 'role', 'shop_id', 'status']);
        $user->save($data);
        
        return $this->success(null, '更新成功');
    }
    
    public function delete($id)
    {
        if (!$this->checkPermission('user_manage')) {
            return $this->error('权限不足');
        }
        
        $user = User::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        if ($user->id == $this->userId) {
            return $this->error('不能删除自己');
        }
        
        $user->delete();
        
        return $this->success(null, '删除成功');
    }
    
    public function info()
    {
        return $this->success([
            'user_info' => [
                'id' => $this->user->id,
                'nickname' => $this->user->nickname,
                'avatar' => $this->user->avatar,
                'role' => $this->user->role,
                'role_text' => $this->user->role_text,
                'phone' => $this->user->phone
            ]
        ]);
    }
    
    private function getWechatUserInfo($code)
    {
        $appid = \think\facade\Config::get('wechat.appid');
        $secret = \think\facade\Config::get('wechat.secret');
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        
        $result = $this->httpGet($url);
        
        if (isset($result['openid'])) {
            return $result;
        }
        
        return null;
    }
    
    private function generateToken($userId)
    {
        $key = \think\facade\Config::get('jwt.key');
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + 7200
        ];
        
        return \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
    }
}