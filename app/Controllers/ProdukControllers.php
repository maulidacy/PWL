<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProdukControllers extends BaseController
{
    public function index()
    {
        return view('v_produk'); // Load the view for the product page
    }
}
