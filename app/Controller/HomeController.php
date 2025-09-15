<?php
namespace App\Controller;

use Craft\Application\Hash as Hash;
use Craft\Application\Session;

class HomeController extends Controller
{
    public function index()
    {
        $random = rand(1000, 9999);
        flash('message', 'Chào mừng bạn đến với trang chủ! Mã: ' . $random);
        $message = getFlash('message');
        $testHash = Hash::default("password123");
        $testVerify = Hash::verify("password123", $testHash);
        return $this->render(
            'home',
            [
                'message' => $message,
                'testHash' => $testHash,
                'testVerify' => $testVerify
            ]
        );
    }
}