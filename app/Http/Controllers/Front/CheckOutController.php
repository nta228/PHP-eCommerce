<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Mail;

class CheckOutController extends Controller
{
    //
    public function index()
    {
        $carts = Cart::content();
        $total = Cart::total();
        $subtotal =Cart::subtotal();

        return view('front.checkout.index',compact('carts','total','subtotal'));
    }

    public function addOrder(Request $request)
    {
        if ($request->payment_type == 'pay_later'){
            // 1. Thêm đơn hàng
            $order = Order::create($request ->all());

            // 2. Thêm chi tiết đơn hàng
            $carts = Cart::content();

            foreach ($carts as $cart){
                $data = [
                    'order_id'=>$order->id,
                    'product_id'=>$cart->id,
                    'qty'=>$cart->qty,
                    'amount'=>$cart->price,
                    'total'=>$cart->price * $cart->qty,
                ];

                OrderDetail::create($data);
            }
            //3. Send Mail
            $total = Cart::total();
            $subtotal =Cart::subtotal();

            $this->sendEmail($order,$total,$subtotal);


            //4. Delete Order Xóa giỏ hàng
            Cart::destroy();

            //5. Return back Trả về kết  quả
            return "Success !!!";
        }
        else{
            return "Online payment method is not supported. ";
        }

    }

    private function sendEmail($order,$total,$subtotal)
    {
        $email_to = $order->email;

        Mail::send('front.checkout.email',compact('order','total','subtotal'),function ($message) use($email_to) {
            $message->from('codelean@gmail.com','CodeLean eCommerce');
            $message->to($email_to,$email_to);
            $message->subject('Order Notification');
        });
    }
}
