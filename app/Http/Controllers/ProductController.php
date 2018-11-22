<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductLabelp;
use App\ProductSuplier;
use App\Order;
use App\PromApi;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

      /*$api = new PromApi;
      return $api->getList('products', array(
        'group_id' => $group_id,
        'limit' => 100
      ))['products'];*/
      $input = $request->all();

      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 30;
      $products = Product::search($input);

      $stats = array(
          'all' => Product::count(),
          'total' => $products->count(),
          'supliers' => ProductSuplier::all()->count(),
      );
      $products = $products->with('labels')->with('supliers')->orderBy('name')->paginate($per_page);

      $custom = collect([
        'stats' => $stats,
      ]);
      $products = $custom->merge($products);
      return $products;
    }

    public function setPurchasePrice(Request $request)
    {
        $ids = $request->input('ids');
        $price = floatval(str_replace(',','.', $request->input('price')));
        foreach ($ids as $id) {
            $product = Product::find($id);
            $product->purchase_price = $price;
            $product->margin = ($product->price - $price) * 100 / $product->price;
            $product->save();
        }
        return array($ids);
    }

    public function addLabel(Request $request)
    {
        $ids = $request->input('ids');
        $label_ids = $request->input('label_ids');
        foreach ($ids as $id) {
            foreach ($label_ids as $label_id) {
                ProductLabelp::firstOrCreate(array(
                    'product_id' => $id,
                    'labelp_id' => $label_id
                ));
            }
        }
        return array($ids, $label_ids);
    }


    public function removeLabel(Request $request)
    {
        $ids = $request->input('ids');
        $label_ids = $request->input('label_ids');
        foreach ($ids as $id) {
            foreach ($label_ids as $label_id) {
                ProductLabelp::where('product_id', $id)
                    ->where('labelp_id', $label_id)->delete();
            }
        }
        return array($ids, $label_ids);
    }

    public function removeSuplier(Request $request)
    {
        $ids = $request->input('ids');
        $suplier_ids = $request->input('suplier_ids');
        foreach ($ids as $id) {
            foreach ($suplier_ids as $suplier_id) {
                ProductSuplier::where('product_id', $id)
                    ->where('suplier_id', $suplier_id)->delete();
            }
        }
        return array($ids, $suplier_id);
    }

    public function addSuplier(Request $request)
    {
        $ids = $request->input('ids');
        $suplier_ids = $request->input('suplier_ids');
        foreach ($ids as $id) {
            foreach ($suplier_ids as $suplier_id) {
                ProductSuplier::firstOrCreate(array(
                    'product_id' => $id,
                    'suplier_id' => $suplier_id
                ));
            }
        }
        return array($ids, $suplier_id);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
