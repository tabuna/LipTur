<?php

namespace App\Http\Screens\Orders;

use App\Core\Models\Order;
use Orchid\Platform\Screen\Screen;

class OrderList extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Список заказов';
    /**
     * Display header description.
     *
     * @var string
     */
    public $description = 'Список заказов товаров';

    /**
     * Query data.
     *
     * @return array
     */
    public function query() : array
    {
        $orders= Order::orderBy('updated_at','desc')->paginate();
        return [
            'orders' => $orders,
        ];
    }

    /**
     * Button commands.
     *
     * @return array
     */
    public function commandBar() : array
    {
        return [
        ];
    }

    /**
     * Views.
     *
     * @return array
     */
    public function layout() : array
    {
        return [
            OrderListLayout::class,
        ];
    }
}
