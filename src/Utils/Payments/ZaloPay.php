<?php
// not available yet
namespace Craft\Utils\Payments;
/**
 * ZaloPay Utils (Minimal version)
 * @link https://docs.zalopay.vn/v2/docs
 */

class ZaloPay
{
    private $app_id;
    private $key1;
    private $key2;
    private $endpoint;

    public function __construct(array $config)
    {
        $this->app_id   = $config['app_id'];
        $this->key1     = $config['key1'];
        $this->key2     = $config['key2'];
        $this->endpoint = $config['endpoint'] ?? 'https://sb-openapi.zalopay.vn/v2';
    }

    /**
     * Tạo chữ ký (MAC) cho dữ liệu gửi đi
     */
    public function makeMac(string $data, ?string $key = null): string
    {
        return hash_hmac('sha256', $data, $key ?? $this->key1);
    }

    /**
     * Gửi yêu cầu tạo đơn thanh toán
     */
    public function createOrder(array $params): array
    {
        $params['app_id']     = $this->app_id;
        $params['app_trans_id'] = $params['app_trans_id'] ?? date("ymd") . "_" . rand(100000, 999999);
        $params['app_time']   = $params['app_time'] ?? round(microtime(true) * 1000);
        $params['embed_data'] = $params['embed_data'] ?? '{}';
        $params['item']       = $params['item'] ?? '[]';
        $params['mac']        = $this->makeMac(
            $params['app_id'].'|'.$params['app_trans_id'].'|'.$params['app_user'].'|'.$params['amount'].'|'.$params['app_time'].'|'.$params['embed_data'].'|'.$params['item']
        );

        return $this->postJson("/create", $params);
    }

    /**
     * Xác thực callback (ZaloPay server gọi lại)
     */
    public function verifyCallback(array $data): bool
    {
        $mac = $this->makeMac($data['data'], $this->key2);
        return hash_equals($mac, $data['mac']);
    }

    /**
     * Gửi yêu cầu truy vấn trạng thái đơn hàng
     */
    public function queryOrder(string $app_trans_id): array
    {
        $params = [
            'app_id' => $this->app_id,
            'app_trans_id' => $app_trans_id,
        ];
        $params['mac'] = $this->makeMac($params['app_id'].'|'.$params['app_trans_id'].'|'.$this->key1);

        return $this->postJson("/query", $params);
    }

    /**
     * Hàm tiện ích POST JSON
     */
    private function postJson(string $path, array $data): array
    {
        $ch = curl_init($this->endpoint . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            return ['error' => curl_error($ch)];
        }
        curl_close($ch);

        return json_decode($result, true);
    }
}
