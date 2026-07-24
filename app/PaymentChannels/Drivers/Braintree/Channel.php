<?php

namespace App\PaymentChannels\Drivers\Braintree;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Omnipay\Omnipay;

/**
 * NOT PRODUCTION-READY / DO NOT ACTIVATE.
 *
 * This driver sends Omnipay clientToken() (a Drop-in/SDK initialization
 * token) as the payment method nonce ('token' field -> paymentMethodNonce
 * in Omnipay\Braintree\Message\AuthorizeRequest::getData()). A clientToken
 * is NOT a valid payment nonce -- Braintree requires an actual
 * payment-method nonce collected client-side via Braintree Drop-in UI or
 * Hosted Fields after the customer enters real card details.
 *
 * No such frontend integration exists in this codebase (no
 * resources/views/**\/braintree* template was found), so every
 * paymentRequest()/verify() call will send an invalid nonce and the
 * transaction will fail at Braintree's API regardless of credentials.
 *
 * To make this driver production-ready: build a checkout view that loads
 * the Braintree Drop-in/Hosted Fields JS SDK using the clientToken from
 * clientToken(), collects a real payment_method_nonce client-side, and
 * passes that nonce (not the clientToken) into createPaymentData()'s
 * 'token' field. This is a dedicated frontend development task, not a
 * quick fix, and is out of scope for this audit pass.
 */
class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $merchant_id;
    protected $public_key;
    protected $private_key;

    protected array $credentialItems = [
        'merchant_id',
        'public_key',
        'private_key',
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

    protected function makeGateway()
    {
        $gateway = Omnipay::create('Braintree');

        $gateway->setMerchantId($this->merchant_id);
        $gateway->setPublicKey($this->public_key);
        $gateway->setPrivateKey($this->private_key);
        $gateway->setTestMode($this->test_mode);

        return $gateway;
    }

    /**
     * @throws \Exception
     */
    public function paymentRequest(Order $order)
    {
        // Send purchase request
        try {
            $gateway = $this->makeGateway();

            $reqData = $this->createPaymentData($order);
            $reqData['token'] = $gateway->clientToken()->send()->getToken();

            $response = $gateway->purchase($reqData)->send();

        } catch (\Exception $exception) {
//            dd($exception);
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        if ($response->isRedirect()) {
            return $response->redirect();
        }

        $toastData = [
            'title' => trans('cart.fail_purchase'),
            'msg' => '',
            'status' => 'error'
        ];
        return redirect()->back()->with(['toast' => $toastData])->withInput();
    }

    private function createPaymentData($order)
    {
        $generalSettings = getGeneralSettings();
        $user = $order->user;

        $card = [
            'email' => $user->email ?? $generalSettings['site_email'],
            'billingFirstName' => $user->full_name,
            'billingLastName' => '',
            'billingPhone' => $user->mobile,
            'billingCompany' => $generalSettings['site_name'],
            'billingAddress1' => '',
            'billingCity' => '',
            'billingPostcode' => '',
            'billingCountry' => '',
        ];

        return [
            'transactionId' => $order->id,
            'amount' => $this->makeAmountByCurrency($order->total_amount, $this->currency),
            'currency' => $this->currency,
            'testMode' => $this->test_mode,
            'returnUrl' => $this->makeCallbackUrl($order, 'success'),
            'cancelUrl' => $this->makeCallbackUrl($order, 'cancel'),
            'notifyUrl' => $this->makeCallbackUrl($order, 'notify'),
            'card' => $card,
        ];
    }

    private function makeCallbackUrl($order, $status)
    {
        return url("/payments/verify/Braintree?status=$status&order_id=$order->id");
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $order_id = $data['order_id'];

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {
            $orderStatus = Order::$fail;

            // Setup payment gateway
            try {
                $gateway = $this->makeGateway();

                $reqData = $this->createPaymentData($order);
                $reqData['token'] = $gateway->clientToken()->send()->getToken();

                $response = $gateway->purchase($reqData)->send();

            } catch (\Exception $exception) {
                //dd($exception);
                throw new \Exception($exception->getMessage(), $exception->getCode());
            }

            if ($response->isSuccessful()) {
                $orderStatus = Order::$paying;
            }

            $order->update([
                'status' => $orderStatus
            ]);
        }

        return $order;
    }
}
