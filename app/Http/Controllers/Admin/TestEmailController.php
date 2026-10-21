<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class TestEmailController extends Controller
{
    public function test()
    {
        try {
            Mail::raw('Test email dari PMB UBP', function ($message) {
                $message->to('admin@ubpkarawang.ac.id')
                        ->subject('Test Email SMTP')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            
            return response()->json(['success' => true, 'message' => 'Email berhasil dikirim']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}