<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;
use App\Libraries\MoodleRest;


class Boc
{
    /**
     * Ambil informasi site dari Moodle.
     */
    public function core_webservice_get_site_info()
    {
        $MoodleRest = new MoodleRest(
            'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php',
            '9d812708c38116dbbe03864ccb860b52'
        );

        $response = $MoodleRest->request('core_webservice_get_site_info', []);
        return $response;
    }

    /**
     * Membuat user baru di Moodle dan menyimpannya ke tabel no_ujian.
     */
    public function no_ujian($gelombang, $id)
    {
        // Ambil data dari database Laravel (ganti sesuai tabel kamu)
        $data = DB::table('master_virtual')->where('va', $id)->first();

        if (!$data) {
            return false;
        }

        $MoodleRest = new MoodleRest(
            'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php',
            '9d812708c38116dbbe03864ccb860b52'
        );

        $users = [
            'users' => [[
                'username' => $data->va,
                'password' => $data->no_hp,
                'lastname' => $data->nama,
                'firstname' => $data->va,
                'email' => 'pmb' . $data->va . '@pmb.ubpkarawang.ac.id',
                'customfields' => [
                    ['type' => "nip", 'value' => $data->va],
                    ['type' => "klas", 'value' => "usm2020"],
                    ['type' => "nomortelpon", 'value' => $data->no_hp ?? "089797979899"],
                ],
            ]]
        ];

        $response = $MoodleRest->request('core_user_create_users', $users);

        // Jika Moodle memberikan error
        if (isset($response['errorcode'])) {
            return false;
        }

        try {
            // Enrol user ke course Moodle
            $this->update_boc_course($response[0]['id']);

            // Simpan id_elearning ke database
            DB::table('no_ujian')->insert([
                'id_pendaftar' => $id,
                'id_elearning' => $response[0]['id'],
            ]);
        } catch (\Throwable $th) {
            return false;
        }

        return isset($response[0]);
    }

    /**
     * Mendaftarkan user Moodle ke course tertentu.
     */
    public function update_boc_course($id_user)
    {
        $MoodleRest = new MoodleRest(
            'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php',
            '9d812708c38116dbbe03864ccb860b52'
        );

        $enrolments = [
            'enrolments' => [[
                'roleid' => 5, // 5 biasanya untuk student
                'userid' => $id_user,
                'courseid' => 384, // ID course di Moodle
            ]]
        ];

        $MoodleRest->request('enrol_manual_enrol_users', $enrolments);

        return true;
    }
}
