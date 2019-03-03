<?php

namespace App\Http\Screens\Orders;

use Orchid\Screen\Layouts\Rows;

class OrderCartLayout extends Rows
{
    /**
     * @var string
     */
    public $template = 'dashboard.shop.cart.cart';

    /**
     * @return array
     */
    public function fields() : array
    {
        return [

        ];
    }

    /**
     * @param $post
     *
     * @throws \Throwable
     *
     * @return array
     */
    public function build($post)
    {
        return view($this->template, [
            'order'     => $post->getContent('order'),
        ])->render();
    }
}
