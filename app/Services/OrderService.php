<?php

namespace App\Services;

use App\Models\WpPost;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getUserOrders($userId)
    {
        $orders = WpPost::where('post_type', 'shop_order')
            ->where('post_status', 'publish')
            ->whereHas('meta', function ($q) use ($userId) {
                $q->where('meta_key', '_customer_user')
                  ->where('meta_value', $userId);
            })
            ->with('meta')
            ->get();

        return $orders->map(function ($order) {
            return [
                'id' => $order->ID,
                'date' => $order->post_date,
                'status' => $order->post_status,
                'total' => $this->getMetaValue($order, '_order_total'),
                'currency' => $this->getMetaValue($order, '_order_currency')
            ];
        });
    }

    private function getMetaValue($model, $key)
    {
        $meta = $model->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : null;
    }
}