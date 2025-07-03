<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $client;
    protected $apiKey;
    protected $transactionModel;
    protected $transactionDetailModel;
    protected $userModel;

    function __construct()
    {
        helper('number');
        helper('form');
        $this->cart = \Config\Services::cart();
        $this->client = new \GuzzleHttp\Client();
        $this->apiKey = env('COST_KEY');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
        $this->userModel = new \App\Models\UserModel();
    }

    public function index()
    {
        $data['items'] = $this->cart->contents();
        $data['total'] = $this->cart->total();
        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
        $this->cart->insert(array(
            'id'        => $this->request->getPost('id'),
            'qty'       => 1,
            'price'     => $this->request->getPost('harga'),
            'name'      => $this->request->getPost('nama'),
            'options'   => array('foto' => $this->request->getPost('foto'))
        ));
        session()->setFlashdata('success', 'Produk berhasil ditambahkan ke keranjang. (<a href="' . base_url() . 'keranjang">Lihat</a>)');
        return redirect()->to(base_url('/'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();
        session()->setFlashdata('success', 'Keranjang Berhasil Dikosongkan');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $value) {
            $this->cart->update(array(
                'rowid' => $value['rowid'],
                'qty'   => $this->request->getPost('qty' . $i++)
            ));
        }

        session()->setFlashdata('success', 'Keranjang Berhasil Diedit');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);
        session()->setFlashdata('success', 'Keranjang Berhasil Dihapus');
        return redirect()->to(base_url('keranjang'));
    }

    private function calculatePpn($totalHarga)
    {
        return $totalHarga * 0.11;
    }

    private function calculateBiayaAdmin($totalHarga)
    {
        if ($totalHarga <= 20000000) {
            return $totalHarga * 0.006;
        } elseif ($totalHarga <= 40000000) {
            return $totalHarga * 0.008;
        } elseif ($totalHarga > 40000000) {
            return $totalHarga * 0.01;
        }
        return 0;
    }

    public function checkout()
    {
        $totalHarga = $this->cart->total();
        $ppn = $this->calculatePpn($totalHarga);
        $biayaAdmin = $this->calculateBiayaAdmin($totalHarga);

        $ongkir = $this->request->getPost('ongkir') ?? 0;

        $data['items'] = $this->cart->contents();
        $data['total'] = $totalHarga;
        $data['ppn'] = $ppn;
        $data['biaya_admin'] = $biayaAdmin;
        $data['ongkir'] = $ongkir;

        return view('v_checkout', $data);
    }

    public function process_checkout()
    {
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            session()->setFlashdata('error', 'Keranjang kosong, tidak dapat melakukan checkout.');
            return redirect()->to(base_url('keranjang'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $totalHarga = $this->cart->total();
            $ppn = $this->calculatePpn($totalHarga);
            $biayaAdmin = $this->calculateBiayaAdmin($totalHarga);

            $transactionData = [
                'username' => session()->get('username'),
                'total_harga' => $totalHarga,
                'ppn' => $ppn,
                'biaya_admin' => $biayaAdmin,
                'alamat' => $this->request->getPost('alamat'),
                'ongkir' => $this->request->getPost('ongkir'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update user email in profile
            $email = $this->request->getPost('email');
            if ($email) {
                $username = session()->get('username');
                $this->userModel->update(
                    ['username' => $username],
                    ['email' => $email, 'updated_at' => date('Y-m-d H:i:s')]
                );
                // Update email in session
                session()->set('email', $email);
            }

            $this->transactionModel->insert($transactionData);
            $transactionId = $this->transactionModel->getInsertID();

            foreach ($cartItems as $item) {
                $detailData = [
                    'transaction_id' => $transactionId,
                    'product_id' => $item['id'],
                    'jumlah' => $item['qty'],
                    'diskon' => 0,
                    'subtotal_harga' => $item['subtotal'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->transactionDetailModel->insert($detailData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan transaksi.');
            }

            $this->cart->destroy();
            session()->setFlashdata('success', 'Transaksi berhasil diproses.');
            return redirect()->to(base_url('/'));
        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('error', 'Terjadi kesalahan saat memproses transaksi: ' . $e->getMessage());
            return redirect()->to(base_url('keranjang'));
        }
    }

    public function getLocation()
    {
        $search = $this->request->getGet('search');

        try {
            $response = $this->client->request(
                'GET',
                'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=' . $search . '&limit=50',
                [
                    'headers' => [
                        'accept' => 'application/json',
                        'key' => $this->apiKey,
                    ],
                ]
            );

            $body = json_decode($response->getBody(), true);
            return $this->response->setJSON($body['data']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Failed to fetch location data']);
        }
    }

    public function getCost()
    {
        $destination = $this->request->getGet('destination');

        try {
            $response = $this->client->request(
                'POST',
                'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost',
                [
                    'multipart' => [
                        [
                            'name' => 'origin',
                            'contents' => '64999'
                        ],
                        [
                            'name' => 'destination',
                            'contents' => $destination
                        ],
                        [
                            'name' => 'weight',
                            'contents' => '1000'
                        ],
                        [
                            'name' => 'courier',
                            'contents' => 'jne'
                        ]
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                        'key' => $this->apiKey,
                    ],
                ]
            );

        $body = json_decode($response->getBody(), true);
        return $this->response->setJSON($body['data']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Failed to fetch cost data']);
        }
    }
}
