<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SlikBillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Non-aktifkan foreign key check sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $billings = [
            [
                'id' => 1,
                'user_id' => 166,
                'order_id' => 'PMB-UKT-cb6fc2b2-f99b-473e-b518-75720477a031',
                'virtual_account' => 'ERROR-PARSE-VA',
                'amount' => 150000.00,
                'status' => 'pending',
                'payment_ntb' => null,
                'datetime_payment' => null,
                'slik_request_payload' => json_encode([
                    'user' => [
                        'nim' => 18,
                        'name' => 'percobaan',
                        'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'handphone' => '62851124623532'
                    ],
                    'bill_id' => 'PMB-UKT-cb6fc2b2-f99b-473e-b518-75720477a031',
                    'total_bayar' => 150000,
                    'datetime_expired' => '2025-11-13T12:44:56+07:00'
                ]),
                'slik_response_data' => json_encode([
                    'data' => [
                        'nim' => 18,
                        'trx_id' => '7385873599410264',
                        'channel' => 'mandiri',
                        'trx_amount' => 150000,
                        'description' => 'Registrasi_PMB:18-percobaan',
                        'customer_name' => 'Registrasi_PMB:18-percobaan',
                        'customer_email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'customer_phone' => '62851124623532',
                        'virtual_account' => '8968183250198779',
                        'datetime_expired' => '2025-11-13T12:44:56+07:00'
                    ],
                    'status' => 'success',
                    'message' => 'Data received and processed successfully.'
                ]),
                'created_at' => Carbon::parse('2025-11-12 05:44:56'),
                'updated_at' => Carbon::parse('2025-11-12 05:44:56')
            ],
            [
                'id' => 2,
                'user_id' => 166,
                'order_id' => 'PMB-UKT-f99d9fac-0702-457a-b284-f9caa3eded2d',
                'virtual_account' => 'ERROR-PARSE-VA',
                'amount' => 150000.00,
                'status' => 'pending',
                'payment_ntb' => null,
                'datetime_payment' => null,
                'slik_request_payload' => json_encode([
                    'user' => [
                        'nim' => 18,
                        'name' => 'percobaan',
                        'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'handphone' => '62851124623532'
                    ],
                    'bill_id' => 'PMB-UKT-f99d9fac-0702-457a-b284-f9caa3eded2d',
                    'total_bayar' => 150000,
                    'datetime_expired' => '2025-11-13T12:45:24+07:00'
                ]),
                'slik_response_data' => json_encode([
                    'data' => [
                        'nim' => 18,
                        'trx_id' => '5021498740386763',
                        'channel' => 'mandiri',
                        'trx_amount' => 150000,
                        'description' => 'Registrasi_PMB:18-percobaan',
                        'customer_name' => 'Registrasi_PMB:18-percobaan',
                        'customer_email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'customer_phone' => '62851124623532',
                        'virtual_account' => '8968189101387645',
                        'datetime_expired' => '2025-11-13T12:45:24+07:00'
                    ],
                    'status' => 'success',
                    'message' => 'Data received and processed successfully.'
                ]),
                'created_at' => Carbon::parse('2025-11-12 05:45:24'),
                'updated_at' => Carbon::parse('2025-11-12 05:45:24')
            ],
            [
                'id' => 3,
                'user_id' => 166,
                'order_id' => 'PMB-UKT-0dcd3bd4-0c91-4301-a142-c9b219ab19f8',
                'virtual_account' => 'ERROR-PARSE-VA',
                'amount' => 150000.00,
                'status' => 'pending',
                'payment_ntb' => null,
                'datetime_payment' => null,
                'slik_request_payload' => json_encode([
                    'user' => [
                        'nim' => 18,
                        'name' => 'percobaan',
                        'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'handphone' => '62851124623532'
                    ],
                    'bill_id' => 'PMB-UKT-0dcd3bd4-0c91-4301-a142-c9b219ab19f8',
                    'total_bayar' => 150000,
                    'datetime_expired' => '2025-11-13T12:48:18+07:00'
                ]),
                'slik_response_data' => json_encode([
                    'data' => [
                        'nim' => 18,
                        'trx_id' => '7982154530136064',
                        'channel' => 'mandiri',
                        'trx_amount' => 150000,
                        'description' => 'Registrasi_PMB:18-percobaan',
                        'customer_name' => 'Registrasi_PMB:18-percobaan',
                        'customer_email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'customer_phone' => '62851124623532',
                        'virtual_account' => '8968118449651780',
                        'datetime_expired' => '2025-11-13T12:48:18+07:00'
                    ],
                    'status' => 'success',
                    'message' => 'Data received and processed successfully.'
                ]),
                'created_at' => Carbon::parse('2025-11-12 05:48:18'),
                'updated_at' => Carbon::parse('2025-11-12 05:48:18')
            ],
            [
                'id' => 4,
                'user_id' => 166,
                'order_id' => 'PMB-UKT-e63e705e-239e-4e1d-920b-98608622004a',
                'virtual_account' => '8968161279463502',
                'amount' => 150000.00,
                'status' => 'pending',
                'payment_ntb' => '987654321',
                'datetime_payment' => Carbon::parse('2025-11-12 14:27:12'),
                'slik_request_payload' => json_encode([
                    'user' => [
                        'nim' => 18,
                        'name' => 'percobaan',
                        'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'handphone' => '62851124623532'
                    ],
                    'bill_id' => 'PMB-UKT-e63e705e-239e-4e1d-920b-98608622004a',
                    'total_bayar' => 150000,
                    'datetime_expired' => '2025-11-13T12:57:29+07:00'
                ]),
                'slik_response_data' => json_encode([
                    'data' => [
                        'nim' => 18,
                        'trx_id' => '5208632197463914',
                        'channel' => 'mandiri',
                        'trx_amount' => 150000,
                        'description' => 'Registrasi_PMB:18-percobaan',
                        'customer_name' => 'Registrasi_PMB:18-percobaan',
                        'customer_email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'customer_phone' => '62851124623532',
                        'virtual_account' => '8968161279463502',
                        'datetime_expired' => '2025-11-13T12:57:29+07:00'
                    ],
                    'status' => 'success',
                    'message' => 'Data received and processed successfully.'
                ]),
                'created_at' => Carbon::parse('2025-11-12 05:57:29'),
                'updated_at' => Carbon::parse('2025-11-12 07:27:12')
            ],
            [
                'id' => 5,
                'user_id' => 166,
                'order_id' => 'PMB-UKT-452b04c7-22ea-4676-ac36-4189e04f6ef9',
                'virtual_account' => '8968163158594029',
                'amount' => 150000.00,
                'status' => 'paid',
                'payment_ntb' => '987654321',
                'datetime_payment' => Carbon::parse('2025-11-12 15:34:31'),
                'slik_request_payload' => json_encode([
                    'user' => [
                        'nim' => 18,
                        'name' => 'percobaan',
                        'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'handphone' => '62851124623532'
                    ],
                    'bill_id' => 'PMB-UKT-452b04c7-22ea-4676-ac36-4189e04f6ef9',
                    'total_bayar' => 150000,
                    'datetime_expired' => '2025-11-13T14:34:35+07:00'
                ]),
                'slik_response_data' => json_encode([
                    'data' => [
                        'nim' => 18,
                        'trx_id' => '7071454521969832',
                        'channel' => 'mandiri',
                        'trx_amount' => 150000,
                        'description' => 'Registrasi_PMB:18-percobaan',
                        'customer_name' => 'Registrasi_PMB:18-percobaan',
                        'customer_email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                        'customer_phone' => '62851124623532',
                        'virtual_account' => '8968163158594029',
                        'datetime_expired' => '2025-11-13T14:34:35+07:00'
                    ],
                    'status' => 'success',
                    'message' => 'Data received and processed successfully.'
                ]),
                'created_at' => Carbon::parse('2025-11-12 07:34:35'),
                'updated_at' => Carbon::parse('2025-11-12 08:34:31')
            ]
        ];

        // Kosongkan tabel terlebih dahulu
        DB::table('slik_billings')->truncate();

        // Insert data
        foreach ($billings as $billing) {
            DB::table('slik_billings')->insert($billing);
        }

        // Aktifkan kembali foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}