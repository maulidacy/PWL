<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Home extends BaseController
{
    protected $product;

    public function __construct()
    {
        helper('form');
        helper('number');
        $this->product = new ProductModel();
    }

    public function index(): string
    {
        $perPage = 10;
        $currentPage = $this->request->getVar('page') ?? 1;

        $products = $this->product->paginate($perPage, 'default', $currentPage);
        $pager = $this->product->pager;

        $data = [
            'products' => $products,
            'pager' => $pager
        ];

        return view('v_home', $data);
    }

    public function profile(): string
    {
        $session = session();
        $username = $session->get('username');

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('username', $username)->first();

        $data = [
            'username' => $username,
            'role' => $session->get('role'),
            'email' => $user ? $user['email'] : '',
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
