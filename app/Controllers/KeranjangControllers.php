<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class KeranjangControllers extends BaseController
{
    protected $cart;

    public function __construct()
    {
        helper('number');
        helper('form');
        $this->cart = \Config\Services::cart();
    }

    public function index()
    {
        $data['items'] = $this->cart->contents();
        $data['total'] = $this->cart->total();
        return view('v_keranjang', $data); // Load the view for the cart page with data
    }
}
