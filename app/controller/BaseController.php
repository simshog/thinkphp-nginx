<?php
namespace app\controller;

use think\Request;
use think\Response;
use think\facade\Config;
use app\model\User;
use app\model\Permission;

class BaseController
{
    protected $request;
    protected $user;
    protected $userId;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initialize();
    }
    
    protected function initialize()
    {
        $this->checkAuth();
    }
    
    protected function checkAuth()
    {
        $token = $this->request->header('Authorization');
        if (!$token) {
            $this->error('未授权访问', 401);
        }
        
        $user = $this->getUserByToken($token);
        if (!$user) {
            $this->error('登录已过期', 401);
        }
        
        $this->user = $user;
        $this->userId = $user->id;
    }
    
    protected function getUserByToken($token)
    {
        $token = str_replace('Bearer ', '', $token);
        
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, Config::get('jwt.key'), ['HS256']);
            return User::find($decoded->user_id);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    protected function checkPermission($permissionCode)
    {
        if (!$this->user) {
            return false;
        }
        
        $role = $this->user->role;
        $permission = Permission::where('code', $permissionCode)->find();
        
        if (!$permission) {
            return false;
        }
        
        $rolePermissions = \think\facade\Db::name('role_permissions')
            ->where('role', $role)
            ->where('permission_code', $permissionCode)
            ->find();
            
        return !empty($rolePermissions);
    }
    
    protected function success($data = [], $msg = '操作成功', $code = 200)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        
        return json($result);
    }
    
    protected function error($msg = '操作失败', $code = 400, $data = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        
        return json($result, $code);
    }
    
    protected function generateQrCode($wifiId)
    {
        $scene = 'wifi_' . $wifiId;
        $page = 'pages/connect/connect';
        
        $accessToken = $this->getWechatAccessToken();
        if (!$accessToken) {
            return null;
        }
        
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $accessToken;
        
        $postData = [
            'scene' => $scene,
            'page' => $page,
            'width' => 430,
            'auto_color' => false,
            'line_color' => ['r' => 0, 'g' => 0, 'b' => 0],
            'is_hyaline' => false
        ];
        
        $result = $this->httpPost($url, json_encode($postData));
        
        if (isset($result['errcode'])) {
            return null;
        }
        
        $filename = 'qrcode_' . $wifiId . '_' . time() . '.png';
        $filepath = public_path() . 'uploads/qrcode/' . $filename;
        
        file_put_contents($filepath, $result);
        
        return '/uploads/qrcode/' . $filename;
    }
    
    protected function getWechatAccessToken()
    {
        $appid = Config::get('wechat.appid');
        $secret = Config::get('wechat.secret');
        
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        
        $result = $this->httpGet($url);
        
        if (isset($result['access_token'])) {
            return $result['access_token'];
        }
        
        return null;
    }
    
    protected function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    protected function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}