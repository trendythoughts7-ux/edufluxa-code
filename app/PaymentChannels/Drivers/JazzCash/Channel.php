<?php

namespace App\PaymentChannels\Drivers\JazzCash;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $merchant_id;
    protected $password;
    protected $integerity_salt;
    protected $endpoint;
    protected $return_url;


    protected array $credentialItems = [
        "merchant_id",
        "password",
        "integerity_salt",
        "endpoint",
    ];


    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->return_url = url("/payments/verify/JazzCash");
        $this->setCredentialItems($paymentChannel);
    }

    private function handleConfigs()
    {
        $mode = $this->test_mode ? 'sandbox' : 'live';

        \Config::set("jazzcash.environment", $mode);
        \Config::set("jazzcash.{$mode}.merchant_id", $this->merchant_id);
        \Config::set("jazzcash.{$mode}.password", $this->password);
        \Config::set("jazzcash.{$mode}.integerity_salt", $this->integerity_salt);
        \Config::set("jazzcash.{$mode}.return_url", $this->return_url);
        \Config::set("jazzcash.{$mode}.endpoint", $this->endpoint);

    }

    /**
     * @throws \Exception
     */
    public function paymentRequest(Order $order)
    {
        $this->handleConfigs();

        // Send purchase request
        try {

            $data = \AKCybex\JazzCash\Facades\JazzCash::request()
                ->setAmount($this->makeAmountByCurrency($order->total_amount, $this->currency))
                ->toArray();

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        $data['ppmpf_1'] = $order->id;
        $data['ppmpf_2'] = $order->user_id;

        return view('design_1.web.cart.payment.channels.jazzCash', ['data' => $data]);
    }

    private function makeCallbackUrl($order, $status)
    {
        return url("/payments/verify/JazzCash?status=$status&order_id=$order->id");
    }

    public function verify(Request $request)
    {
        $this->handleConfigs();

        try {

            if (!$this->hasValidSecureHash($request)) {
                \Log::warning('JazzCash: invalid or missing pp_SecureHash on verify callback', [
                    'ppmpf_1' => $request->get('ppmpf_1'),
                    'ip'      => $request->ip(),
                ]);
                return null;
            }

            $orderId = $request->get('ppmpf_1');
            $buyerId = $request->get('ppmpf_2');

            $order = Order::where('id', $orderId)
                ->where('user_id', $buyerId)
                ->first();

            if (!empty($order)) {

                if ($order->status == Order::$paying) {
                    return $order;
                }

                Auth::loginUsingId($buyerId);

                $jazzcash = \AKCybex\JazzCash\Facades\JazzCash::response();

                $orderStatus = ($jazzcash->code() == 000) ? Order::$paying : Order::$fail;

                $order->update([
                    'status' => $orderStatus,
                ]);
            }

            return $order ?? null;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Recomputes JazzCash's HMAC-SHA256 secure hash server-side using the
     * merchant's own stored integrity salt (never trusts a client-submitted
     * pp_HashKey), and compares it timing-safely against the pp_SecureHash
     * the client submitted. This mirrors AKCybex\JazzCash's own
     * generateSecureHash() field-selection and string-building logic exactly.
     */
    private function hasValidSecureHash(Request $request): bool
    {
        $providedHash = $request->get('pp_SecureHash');

        if (empty($providedHash) || empty($this->integerity_salt)) {
            return false;
        }

        $data = $request->except(['pp_SecureHash', 'pp_HashKey']);
        ksort($data);

        $str = '';
        foreach ($data as $value) {
            if (!empty($value) && !is_array($value)) {
                $str .= '&' . $value;
            }
        }
        $str = $this->integerity_salt . $str;

        $expectedHash = hash_hmac('sha256', $str, $this->integerity_salt);

        return hash_equals($expectedHash, (string) $providedHash);
    }
}
