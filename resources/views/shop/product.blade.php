@extends('layouts.shop')
@section('title','Магазин')
@section('description','Магазин')
@section('keywords','Магазин')
@section('shop')

    <section class="container-lg">
        <div class="row">
            <div class="bg-bordo">
                <div class="container">
                    <h1 class="brand-header">Интернет-магазин</h1>
                </div>
            </div>
            <nav>
                <div class="container">
                    @include('partials.breadcrumb',[ 'breadcrumb' => [], 'current' => 'Магазин' ])
                </div>
            </nav>
        </div>
    </section>

    <section class="b-b box-shadow" id="product">
        <div class="container padder-v ">
            <div class="row">
                <div class="block-header">
                    {{$product->getContent('name')}}
                </div>
            </div>
            <div class="row">

                <main class="col-md-8">

                    {!! $product->getContent('body') !!}


                </main>
                <div class="col-md-4">

                    <div class="bg-white w-full b">

                        <div class="wrapper">
                                <div class="v-center">
                                    <div class="col-xs-6 no-padder text-black m-t">Цена:</div>
                                    <div class="col-xs-6 no-padder text-right h3 text-danger">988 руб.</div>
                                </div>
                        </div>

                        <div class="wrapper b-t">
                            <button class="btn btn-success btn-group-justified text-u-c" v-on:click="addIntoCart('{{$product->id}}')">в корзину</button>

                            <div class="padder-v v-center text-lg">
                                       <span id="stars-existing" class="starrr text-warning-lt"
                                             {{-- $rating->percent / $item->id --}}
                                             data-rating='3' data-post-id='1'
                                             style="cursor: pointer;"></span>
                                <small class="m-l-sm"> Средний рейтинг 3 </small>
                            </div>
                        </div>

                        <div class="wrapper b-t">
                            <ul class="list">
                                <li>Артикул: <span>1967811</span></li>
                                <li>Продавец: <span>“Елецие кружева”, г. Елец</span></li>
                            </ul>

                        </div>
                    </div>

                    <div class="m-t-md">
                        <div class="b-t">
                            <div class="wrapper">
                                <div class="v-center">
                                    <div class="thumb-sm m-r-md">
                                        <img src="/img/shop/feature-1.png" class="img-responsive center">
                                    </div>
                                    <span>Удобная и быстрая доставка<br> по всей России</span>
                                </div>
                            </div>
                        </div>
                        <div class="b-t">
                            <div class="wrapper">
                                <div class="v-center">
                                    <div class="thumb-sm m-r-md">
                                        <img src="/img/shop/feature-2.png" class="img-responsive center">
                                    </div>
                                    <span>30 дней на возврат товара</span>
                                </div>
                            </div>
                        </div>
                        <div class="b-t">
                            <div class="wrapper">
                                <div class="v-center">
                                    <div class="thumb-sm m-r-md">
                                        <img src="/img/shop/feature-3.png" class="img-responsive center">
                                    </div>
                                    <span>Гарантия подлинности и качества</span>
                                </div>
                            </div>
                        </div>
                        <div class="b-t">
                            <div class="wrapper">
                                <div class="v-center">
                                    <div class="thumb-sm m-r-md">
                                        <img src="/img/shop/feature-4.png" class="img-responsive center">
                                    </div>
                                    <span>Все товары и цены - <br>напрямую от производителей</span>
                                </div>
                            </div>
                        </div>
                        <div class="b-t b-b">
                            <div class="wrapper">
                                <div class="v-center">
                                    <div class="thumb-sm m-r-md">
                                        <img src="/img/shop/feature-5.png" class="img-responsive center">
                                    </div>
                                    <span>Безопасность платежей гарантируется<br>использованием SSL протокола</span>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

            </div>
        </div>
    </section>
@endsection