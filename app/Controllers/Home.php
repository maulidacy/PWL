<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class Home extends BaseController
{
    protected $product;
    protected $transaction;
    protected $transactionDetail;

    public function __construct()
    {
        helper('form');
        helper('number');
        $this->product = new ProductModel();
        $this->transaction = new TransactionModel();
        $this->transactionDetail = new TransactionDetailModel();
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

public function profile1()
{
    $session = session();
    $username = $session->get('username');
    $role = $session->get('role');

    $data['username'] = $username;

    $buy = $this->transaction->where('username', $username)->findAll();
    $data['buy'] = $buy;

    $product = [];

    if (!empty($buy)) {
        foreach ($buy as $item) {
            $detail = $this->transactionDetail->select('transaction_detail.*, product.nama, product.harga, product.foto')->join('product', 'transaction_detail.product_id=product.id')->where('transaction_id', $item['id'])->findAll();

            if (!empty($detail)) {
                $product[$item['id']] = $detail;
            }
        }
    }

    $data['product'] = $product;

    $userModel = new \App\Models\UserModel();
    $user = $userModel->where('username', $username)->first();

    $data['role'] = $role;
    $data['email'] = $user ? $user['email'] : '';
    $data['login_time'] = $session->get('login_time');
    $data['isLoggedIn'] = $session->get('isLoggedIn');

    return view('v_profile1', $data);
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
