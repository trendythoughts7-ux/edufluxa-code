<?php

namespace App\PaymentChannels\Drivers\Cashu;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use CashUAony\Phpanonymous\CashU;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $encryption_key;
    protected $merchant_id;

    protected array $credentialItems = [
        'merchant_id',
        'encryption_key',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->setCredentialItems($paymentChannel);
    }

    private function handleConfigs()
    {
        \Config::set('cashu._merchant_id', $this->merchant_id);
        \Config::set('cashu._encryption_key', $this->encryption_key);
        \Config::set('cashu._testmod', $this->test_mode);
    }

    public function paymentRequest(Order $order)
    {
        $this->handleConfigs();
        \Config::set('cashu._session_id', 'order_' . $order->id);

        $generalSettings = getGeneralSettings();
        $user = $order->user;

        $data = [
            'amount' => $this->makeAmountByCurrency($order->total_amount, $this->currency),
            'currency' => $this->currency,
            'display_text' => $generalSettings['site_name'].' payment',
            'lang' => 'ar', // language arabic or english ( ar , en ) for lowercase
            'item1' => 'order '.$order->id,
            'item2' => '',
            'item3' => '',
            'item4' => '',
            'item5' => '',
            'service_name' => 'PaymentPhpAnonymous', // service name with setup on your account
        ];

        return CashU::Go($data);
    }

    public function verify(Request $request)
    {
        $this->handleConfigs();

        $data = $request->all();
        $sessionId = $data['session_id'] ?? null;
        $orderId = null;

        if (!empty($sessionId) && str_starts_with($sessionId, 'order_')) {
            $orderId = substr($sessionId, strlen('order_'));
        }

        $order = !empty($orderId) ? Order::find($orderId) : null;

        if (!empty($order)) {
            // NOTE: CashU's callback payload does not clearly document a
            // reliable success/fail indicator (see vendor package README).
            // Orders are marked failed by default until this is verified
            // against real CashU documentation. Do not activate this
            // gateway until success-detection logic is properly confirmed.
            $order->update([
                'status' => Order::$fail,
            ]);
        }

        return $order;
    }
}
