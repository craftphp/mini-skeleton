<?php
// not available yet
namespace Craft\Application;
/**
 * #### OAuth login Google
 * 
 * Usage of OAuth to log in with Google.
 * 
 */

class OAuth
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = env('GOOGLE_CLIENT_ID');
        $this->clientSecret = env('GOOGLE_CLIENT_SECRET');
        $this->redirectUri = env('GOOGLE_REDIRECT_URI');
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'offline',
            'prompt'        => 'select_account'
        ]);
        return "https://accounts.google.com/o/oauth2/auth?$params";
    }

    public function getAccessToken(string $code): ?array
    {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code'
        ];
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query($data)
            ]
        ];
        $response = file_get_contents($url, false, stream_context_create($options));
        return $response ? json_decode($response, true) : null;
    }

    public function getUserInfo(string $accessToken): ?array
    {
        $context = stream_context_create([
            'http' => ['header' => "Authorization: Bearer $accessToken"]
        ]);
        $response = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, $context);
        return $response ? json_decode($response, true) : null;
    }
}

// ======== Cấu hình ========
$clientId     = 'YOUR_GOOGLE_CLIENT_ID';
$clientSecret = 'YOUR_GOOGLE_CLIENT_SECRET';
$redirectUri  = 'http://localhost/OAuth.php'; // đổi cho phù hợp

$oauth = new OAuth();

// ======== Luồng xử lý ========
if (!isset($_GET['code'])) {
    // Bước 1: Chưa có code -> chuyển hướng tới Google login
    header('Location: ' . $oauth->getAuthUrl());
    exit;
} else {
    // Bước 2: Đã có code -> đổi access_token và lấy thông tin user
    $tokenData = $oauth->getAccessToken($_GET['code']);
    if (!$tokenData || !isset($tokenData['access_token'])) {
        exit('Lỗi: không thể lấy access token!');
    }
    $user = $oauth->getUserInfo($tokenData['access_token']);
    echo "<pre>";
    echo "✅ Đăng nhập Google thành công!\n\n";
    print_r($user);
    echo "</pre>";
}
