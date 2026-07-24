<?php

namespace App\PaymentChannels\Drivers\Payfort;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $command;
    protected $access_code;
    protected $merchant_identifier;
    protected $merchant_reference;
    protected $signature;
    protected $test_mode;

    protected array $credentialItems = [
        'command',
        'access_code',
        'merchant_identifier',
        'merchant_reference',
        'signature',
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

    public function paymentRequest(Order $order)
    {
        $user = $order->user;
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $generalSettings = getGeneralSettings();
        $currency = currency();

        $requestParams = array(
            'command' => $this->command,
            'access_code' => $this->access_code,
            'merchant_identifier' => $this->merchant_identifier,
            'merchant_reference' => $this->merchant_reference,
            'amount' => $price,
            'currency' => $currency,
            'language' => 'en',
            'customer_email' => $user->email ?? $generalSettings['site_email'],
            'signature' => $this->signature,
            'order_description' => $generalSettings['site_name'] . ' payment',
        );

        $redirectUrl = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';
        echo "<html xmlns='https://www.w3.org/1999/xhtml'>\n<head></head>\n<body>\n";
        echo "<form action='$redirectUrl' method='post' name='frm'>\n";
        foreach ($requestParams as $a => $b) {
            echo "\t<input type='hidden' name='" . htmlentities($a) . "' value='" . htmlentities($b) . "'>\n";
        }
        echo "\t<script type='text/javascript'>\n";
        echo "\t\tdocument.frm.submit();\n";
        echo "\t</script>\n";
        echo "</form>\n</body>\n</html>";
    }

    private function makeCallbackUrl($order, $status)
    {

    }

    /**
     * NOT PRODUCTION-READY. Do not activate this gateway.
     *
     * Known gaps found during Session 018 audit:
     * - paymentRequest() uses a static 'signature' credential item as-is,
     *   but PayFort's real flow requires a signature dynamically computed
     *   per-request (hash of the request params + merchant secret phrase,
     *   per PayFort's SHA signature spec). As written, PayFort will reject
     *   real transactions with a signature mismatch.
     * - paymentRequest() never stores the order id in session (every other
     *   driver does this via order_session_key), so there is no way to
     *   look the order back up on callback.
     * - makeCallbackUrl() is an empty stub.
     * - verify() below was an unimplemented stub (previously dd(2), which
     *   halted every request) - the dd() has been removed so it no longer
     *   crashes, but it still does not read the callback request, verify
     *   any signature, or look up a real order. It always returns null.
     *
     * Do not flip this gateway's status to active until all of the above
     * are implemented and tested against real PayFort sandbox credentials.
     */
    public function verify(Request $request)
    {
        return null;
    }
}
