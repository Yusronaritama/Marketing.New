public function callback()
    {

        $this->load->library('bnienc');
        $client_id = $this->client_id;
        $secret_key = $this->secret_key;
        $data = file_get_contents('php://input');
        $client = new Client();
        /*$data =json_encode( array(
			"data"=>"HRYdRkZNGEUcGEZiNFZcCklTTQVVNBpMGx0eGCJLEhcOEQQOfAddB0VXdkV5RFYLV1sPHDpOGkkYIR8WHk0cGBkfEkZJNhI4Rl0OVwJSSwVMVEtTfDUmNSZPXkNfNC41NgYfNGZhP15AB1kzVisrMi8wLjFfWwtDBV9bWUl5T1ZhU1Y6TTcfQhQbRxQ4DQkGSmBaRwYKR3VUWVtTXTcgCB8ZEUVHRAhCBUsQUAhRRwdWXE9FB3RlAElcWUFOAVJfVloHTjZOFUMXF0gJPgRac2NXTlEPcVcLRQ0jAxpRGB0fFgRENXlKBklfAFF7QFd3YlRSUAw4IjYZGhcdFkUaExobAUZIThhGHRpQBT8HSnRhS15PBHhLA0VnUkdbCEJTW1UdSkRJB00JGUgYSg8aRhcbGzdMQyNJEyUbFhVIGSEcFQQV",
			"client_id"=> "251"
		
		));*/
        $data_json = json_decode($data, true);


        //print_r($data_json);
        if (!$data_json) {
            // handling orang iseng
            echo '{"status":"999","message":"jangan iseng :D"}';
        } else {
            if ($data_json['client_id'] === $client_id) {
                $this->load->model('Mastah');
                $this->load->model('Master_virtual_model');
                $data_asli = $this->bnienc->decrypt(
                    $data_json['data'],
                    $client_id,
                    $secret_key
                );

                if (!$data_asli) {
                    // handling jika waktu server salah/tdk sesuai atau secret key salah
                    echo '{"status":"999","message":"waktu server tidak sesuai NTP atau secret key salah."}';
                } else {
                    // insert data asli ke db
                    /* $data_asli = array(
                            'trx_id' => '', // silakan gunakan parameter berikut sebagai acuan nomor tagihan
                            'virtual_account' => '',
                            'customer_name' => '',
                            'trx_amount' => '',
                            'payment_amount' => '',
                            'cumulative_payment_amount' => '',
                            'payment_ntb' => '',
                            'datetime_payment' => '',
                            'datetime_payment_iso8601' => '',
                        ); */

                    $data_va = array(
                        'va_bni' => $data_asli['virtual_account'],
                        'trx_id' => $data_asli['trx_id'],
                        'payment_ntb' => $data_asli['payment_ntb'],
                        'datetime_payment' => $data_asli['datetime_payment'],
                        'status_va' => 1
                    );
                    $where_array = array(
                        'va_bni' => $data_asli['virtual_account'],
                    );
                    //echo $cek[0]->va;
                    $this->Mastah->update($where_array, $data_va, 'master_virtual');

                    $ambil_va = $this->Master_virtual_model->get_by_id_bni($data_asli['virtual_account']);

                    $this->load->helper('date');
                    $tanggal_sekarang = date('y-m-d');

                    $check = $this->Mastah->cek_va_($ambil_va->va);
                    if ($check) {

                        $datalogin = array(

                            'status' => 1,
                            'tanggal_reg' => $tanggal_sekarang,
                            'input' => 'bni'
                        );
                        $this->Mastah->update_login_va_cuy($datalogin, $ambil_va->va);
                    } else {

                        $data = array(
                            'id' => $ambil_va->va,
                            'pass' => md5($ambil_va->no_hp),
                            'level' => 2,
                            'status' => 1,
                            'tanggal_reg' => date('y-m-d'),
                            'no_ujian' => $ambil_va->va,
                            'input' => 'bni'
                        );

                        /*$datalogin=array(
                            'id'=>$ambil_va->va,
                            'pass'=>md5($ambil_va->no_hp),
                            'level'=>2,
                            'status'=>1,
                            'tanggal_reg'=>$tanggal_sekarang,
                            'no_ujian'=>$ambil_va->va,
                            'input'=>'bni'
                            );
							$this->Mastah->insert_login($datalogin); */

                        $this->Mastah->insert_login_va_cuy($data);
                    }


                    echo '{"status":"000"}';
                    $this->create_elearning_acount($ambil_va->va);

                    // $this->create_nim($ambil_va->va);
                    // $client->request('GET', 'https://pkkmbonline.ubpkarawang.ac.id/kepesertaan/pmb/update');
                    $this->send_sipt();
                    // $this->create_nim('20210065');'21-4162-55201(5 digit prodi)-001(3 digit urut)'
                    exit;
                }
            }
        }
    }