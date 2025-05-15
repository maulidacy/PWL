<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Home extends BaseController
{
    protected $product;

    public function __construct()
    {
        $this->product = new ProductModel();
    }

    public function index(): string
    {
        $products = $this->product->findAll();
        $data['products'] = $products;

        return view('v_home', $data);
    }

    public function profile(): string
    {
        $session = session();
        $data = [
            'username' => $session->get('username'),
            'role' => $session->get('role'),
            'email' => $session->get('email'),
            'login_time' => $session->get('login_time'),
            'isLoggedIn' => $session->get('isLoggedIn'),
        ];
        return view('v_profile', $data);
    }

    public function faq(): string
    {
        return view('v_faq');
    }
}
