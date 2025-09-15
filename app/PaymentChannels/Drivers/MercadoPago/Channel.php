<?php

namespace App\PaymentChannels\Drivers\MercadoPago;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference\Preference as MercadoPreference;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $public_key;
    protected $access_token;
    protected $client_id;
    protected $client_secret;
    protected $order_session_key;

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();

        $this->public_key = env('MERCADO_PAGO_PUBLIC_KEY');
        $this->access_token = env('MERCADO_PAGO_ACCESS_TOKEN');
        $this->client_id = env('MERCADO_CLIENT_ID');
        $this->client_secret = env('MERCADO_CLIENT_SECRET');

        $this->order_session_key = 'mercado.payments.order_id';
    }

    public function paymentRequest(Order $order)
    {
        $user = $order->user;

        // Configure MercadoPago SDK v3
        MercadoPagoConfig::setAccessToken($this->access_token);

        $orderItems = $order->orderItems;

        $items = [];
        foreach ($orderItems as $orderItem) {
            $items[] = [
                "id" => (string)$orderItem->id,
                "title" => "item " . $orderItem->id,
                "quantity" => 1,
                "unit_price" => (float)$this->makeAmountByCurrency($orderItem->total_amount, $this->currency),
                "currency_id" => $this->currency,
            ];
        }

        $preferenceData = [
            "items" => $items,
            "payer" => [
                "name" => $user->full_name,
                "email" => $user->email,
                "phone" => [
                    "area_code" => "",
                    "number" => $user->mobile
                ]
            ],
            "back_urls" => $this->makeCallbackUrl($order),
            "auto_return" => "approved"
        ];

        /*$preferenceData["payment_methods"] = [
            "excluded_payment_types" => [
                ["id" => "credit_card"]
            ],
            "installments" => 12
        ];*/

        $client = new PreferenceClient();
        $preference = $client->create($preferenceData);

        session()->put($this->order_session_key, $order->id);

//        return $preference->sandbox_init_point;
        $data = [
            'public_key' => $this->public_key,
            'preference_id' => $preference->id,
        ];

        return view('web.default.cart.channels.mercado', $data);
    }

    private function makeCallbackUrl($order)
    {
        return [
            'success' => url("/payments/verify/MercadoPago"),
            'failure' => url("/payments/verify/MercadoPago"),
            'pending' => url("/payments/verify/MercadoPago"),
        ];
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $status = $data['status']; // approved or pending

        $order_id = session()->get($this->order_session_key, null);
        session()->forget($this->order_session_key);

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {

            if ($status == 'approved') {
                $order->update([
                    'status' => Order::$paying,
                    'payment_data' => json_encode($data),
                ]);

                return $order;
            }


            $order->update([
                'status' => Order::$fail,
                'payment_data' => json_encode($data),
            ]);
        }

        return $order;
    }
}
