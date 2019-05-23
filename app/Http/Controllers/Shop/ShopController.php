<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Region;
use App\Models\Master;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
		$regionl = Region::all();
        $newsAndSpecial = Post::type('product')
            ->with('attachment')
            ->where(function ($q) {
                $q->where('options->new', '!=', "0")
                    ->orWhere('options->special', '!=', "0");
            })
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
            ->get()->take(4);

        $warnings = Post::type('product')
            ->with('attachment')
            ->where('options->warning', '!=', "0")
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
            ->get()->take(8);

        $categories = ShopCategory::all();

        $topslider = Post::type('shopslider')
            ->with('attachment')
            ->get();

        return view('shop.index', [
            'newsAndSpecial' => $newsAndSpecial,
            'warnings'       => $warnings,
            'categories'     => $categories,
            'topslider'      => $topslider,
			'regionlists'    => $regionl,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mostPopular()
    {
        $newsAndSpecial = Post::type('product')
            ->with('attachment')
            ->where(function ($q) {
                $q->where('options->new', '!=', "0")
                    ->orWhere('options->special', '!=', "0");
            })
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
            ->get();

        return view('shop.index', [
            'newsAndSpecial' => $newsAndSpecial,
        ]);
    }

    /**
     * @return View
     */
    public function catalog(): View
    {
        $categories = ShopCategory::all();

        return view('shop.catalog', [
            'categories' => $categories,
        ]);
    }

    /**
     * @param \App\Models\Post $product
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function product(Post $product): View
    {
        $warnings = Post::type('product')
            ->with('attachment')
            ->where('options->warning', '!=', "0")
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
            ->get();

        $category = optional($product->taxonomies()->first())->term ?? new Term();

        $comments = $product->comments()->where('approved', 1)->orderBy('created_at', 'DESC')->get();

        return view('shop.product', [
            'product'  => $product,
            'warnings' => $warnings,
            'category' => $category,
            'comments' => $comments,
        ]);
    }

    /**
     * @param string $slug
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function products(string $slug, Request $request): View
    {
        $categories = ShopCategory::all();
        $category   = ShopCategory::slug($slug)->first();

        $products = $category->posts()
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0");

        if (!is_null($request->get('sort'))) {
            $sort    = $request->get('sort');
            $asort   = [
                'price_asc'  => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true],
                'price_desc' => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'desc', true],
                'name_asc'   => ['content->ru->name', 'asc', false],
                'name_desc'  => ['content->ru->name', 'desc', false],
            ];
            $orderBy = $asort[$sort];
        } else {
            $orderBy = ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true];
        }
        if ($orderBy[2]) {
            $products = $products->orderByRaw($orderBy[0] . $orderBy[1]);
        } else {
            $products = $products->orderBy($orderBy[0], $orderBy[1]);
        }

        $products = $products->paginate($request->get('perpage') ?? 15)
            ->appends($request->all());
			
        return view('shop.products', [
            'categories'      => $categories,
            'currentCategory' => $category,
            'products'        => $products,
            'request'         => $request->all(),
        ]);
    }

    /**
     * @param string $slug
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function newsproducts(Request $request): View
    {
        if (!is_null($request->get('search'))) {
            $products = Post::type('product')
                ->whereRaw('LOWER(`content`) LIKE \'%' . mb_strtolower($request->get('search')) . '%\' ')
                ->whereNotNull('options->count')
                ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
                ->with('attachment');
        } else {
            $products = Post::type('product')
                ->with('attachment')
                ->where(function ($q) {
                    $q->where('options->new', '!=', "0")
                        ->orWhere('options->special', '!=', "0");
                })
                ->whereNotNull('options->count')
                ->whereRaw("CAST(options->'$.count' AS SIGNED) >0")
                ->where('status', '<>', 'hidden');
        }

        $categories = ShopCategory::all();

        if (!is_null($request->get('sort'))) {
            $sort    = $request->get('sort');
            $asort   = [
                'price_asc'  => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true],
                'price_desc' => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'desc', true],
                'name_asc'   => ['content->ru->name', 'asc', false],
                'name_desc'  => ['content->ru->name', 'desc', false],
            ];
            $orderBy = $asort[$sort];
        } else {
            //$orderBy=['created_at','asc',false];
            $orderBy = ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true];
        }
        if ($orderBy[2]) {
            $products = $products->orderByRaw($orderBy[0] . $orderBy[1]);
        } else {
            $products = $products->orderBy($orderBy[0], $orderBy[1]);
        }

        $products = $products->paginate($request->get('perpage') ?? 15)
            ->appends($request->all());

        return view('shop.products', [
            'categories'      => $categories,
            'currentCategory' => $categories[0],
            'products'        => $products,
            'request'         => $request->all(),
            'newsAndSpec'     => true,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cart(): View
    {
        return view('shop.cart');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function order(): View
    {
        return view('shop.order.order');
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function purchase(): View
    {
        if (Auth::check()) {
            Cart::restore(Auth::id());
        }

        $cart = get_cart_content(Cart::content(), true);
        return view('shop.order.purchase', [
            'cart' => $cart
        ]);
    }
	/* public function test(int $id, Request $request): View
    {
		$slug = 'suvenirnaya-produkciya';
        $categories = ShopCategory::all();
        $category   = ShopCategory::slug($slug)->first();
		
		$masterCategory = $id;
		$categoriesMaster = Region::orderBy('content')->get();
		$catregion = Region::all();
		$categoryname = $catregion[$id-1]->content;
		$categoryimg = $catregion[$id-1]->photo;
		$masterlist = Master::all();
		$masterlistCur = $masterlist
		->where('region_id', '==', $id);
			
			
			$products = $category->posts()
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0");

        if (!is_null($request->get('sort'))) {
            $sort    = $request->get('sort');
            $asort   = [
                'price_asc'  => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true],
                'price_desc' => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'desc', true],
                'name_asc'   => ['content->ru->name', 'asc', false],
                'name_desc'  => ['content->ru->name', 'desc', false],
            ];
            $orderBy = $asort[$sort];
        } else {
            $orderBy = ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true];
        }
        if ($orderBy[2]) {
            $products = $products->orderByRaw($orderBy[0] . $orderBy[1]);
        } else {
            $products = $products->orderBy($orderBy[0], $orderBy[1]);
        }

        $products = $products->paginate($request->get('perpage') ?? 15)
            ->appends($request->all());


        return view('shop.test', [
            'categories'      => $categories,
			'categoriesMaster' => $categoriesMaster,
			'curentMasterCategory' => $id,
            'currentCategory' => $category,
			'currentCategoryName' => $categoryname,
			'currentCategoryImg' => $categoryimg,
            'products'        => $products,
            'request'         => $request->all(),
			'masterlist' => $masterlistCur,
        ]);
    }*/
	
	public function test(int $curId, Request $request): View
    {
		$slug = 'suvenirnaya-produkciya';
		$categories = ShopCategory::all();
        $category   = ShopCategory::slug($slug)->first();

        $products = $category->posts()
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0");
			
			
		if (!is_null($request->get('sort'))) {
            $sort    = $request->get('sort');
            $asort   = [
                'price_asc'  => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true],
                'price_desc' => ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'desc', true],
                'name_asc'   => ['content->ru->name', 'asc', false],
                'name_desc'  => ['content->ru->name', 'desc', false],
            ];
            $orderBy = $asort[$sort];
        } else {
            $orderBy = ["CAST(options->'$.price' AS DECIMAL(10,2)) ", 'asc', true];
        }
        if ($orderBy[2]) {
            $products = $products->orderByRaw($orderBy[0] . $orderBy[1]);
        } else {
            $products = $products->orderBy($orderBy[0], $orderBy[1]);
        }

        $products = $products->paginate($request->get('perpage') ?? 15)
            ->appends($request->all());
			
		$masterlist = Master::all();
        /*$curmaster = $masterlist
		->where('id', '==', $id);*/		
		
		$warnings = Post::type('product')
		->where('content->ru->maintainer', '=', (string)$curId)
		->where('status', '<>', 'hidden')
        ->whereNotNull('options->count')
		->get();

        return view('shop.test', [
		    'products' => $products,
			'categories'      => $categories,
            'currentCategory' => $category,
			'masterlist' => $masterlist,
            'curId' => $curId-1,
			/*'request' => $request->all(),*/
			'warnings' => $warnings,
        ]);
    }
	
    public function masters(int $id, Request $request): View
    {
		$slug = 'suvenirnaya-produkciya';
        $categories = ShopCategory::all();
        $category   = ShopCategory::slug($slug)->first();
		
		$masterCategory = $id;
		$categoriesMaster = Region::orderBy('content')->get();
		$catregion = Region::all();
		$categoryname = $catregion[$id-1]->content;
		$categoryimg = $catregion[$id-1]->photo;
		$masterlist = Master::all();
		$masterlistCur = $masterlist
		->where('region_id', '==', $id);
			
		
        $products = $category->posts()
            ->where('status', '<>', 'hidden')
            ->whereNotNull('options->count')
            ->whereRaw("CAST(options->'$.count' AS SIGNED) >0");


        $products = $products->paginate($request->get('perpage') ?? 15)
            ->appends($request->all());


        return view('shop.masters', [
            'categories'      => $categories,
			'categoriesMaster' => $categoriesMaster,
			'curentMasterCategory' => $id,
            'currentCategory' => $category,
			'currentCategoryName' => $categoryname,
			'currentCategoryImg' => $categoryimg,
            'products'        => $products,
            'request'         => $request->all(),
			'masterlist' => $masterlistCur,
        ]);
    }
	
	 public function masterpage(int $curId): View
    {
		$masterlist = Master::all();
        /*$curmaster = $masterlist
		->where('id', '==', $id);*/

        return view('shop.masterpage', [
			'masterlist' => $masterlist,
            'curId' => $curId-1,
        ]);
    }
}
