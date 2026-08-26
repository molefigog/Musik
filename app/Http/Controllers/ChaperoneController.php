<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cpay\PaymentModule;

class ChaperoneController extends Controller
{
    public function checkout(Request $request, \Cpay\PaymentModule $module)
    {
        $sessionData = $request->session()->all();

        $result = $module->handleRequest($request->all(), $sessionData);

        if (!empty($sessionData['payment'])) {
            $request->session()->put('payment', $sessionData['payment']);
        } else {
            $request->session()->forget('payment');
        }

        return view('chaperone', [
            'message' => $result['message'],
            'showModal' => $result['showModal'],
            'cardHtml' => $result['cardHtml'],
            'payment' => $sessionData['payment'] ?? [],
        ]);
    }
}
