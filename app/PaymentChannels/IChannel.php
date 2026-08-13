<?php

namespace App\PaymentChannels;

use App\Models\Order;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;

interface IChannel
{
    /**
     * IChannel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel);

    /**
     * @param Order $order
     * @return string|Order|null (drivers redirect via URL string on success, or return null/void on failure)
     */
    public function paymentRequest(Order $order);

    /**
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request);

    /**
     * @return array
     * */
    public function getCredentialItems(): array;

}
