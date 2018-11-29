<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductLabelp;
use App\ProductSuplier;
use App\Order;
use App\ProductSuplierLink;
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

      if (isset($input['on_display']) && $input['on_display'] == 'true') {
          $stats = array(
              'all' => Product::where('status', 'on_display')->count(),
              'total' => $products->count(),
              'supliers' => Product::where('status', 'on_display')->has('supliers')->count(),
              'purchase_price' => Product::where('status', 'on_display')->whereNull('purchase_price')->count(),
          );
      } else {
          $stats = array(
              'all' => Product::count(),
              'total' => $products->count(),
              'supliers' => Product::has('supliers')->count(),
              'purchase_price' => Product::whereNull('purchase_price')->count(),
          );
      }
      $products = $products->with(['labels', 'supliers', 'suplierlinks'])->withCount('orders')->orderBy('name')->paginate($per_page);

      $custom = collect([
        'stats' => $stats,
      ]);
      $products = $custom->merge($products);
      return $products;
    }

    public function massUpdate(Request $request)
    {
        $ids = $request->input('ids');
        $name = $request->input('name');
        $value = $request->input('value');
        if ($name == 'purchase_price') {
            $value = floatval(str_replace(',','.', $value));
        }
        foreach ($ids as $id) {
            $product = Product::find($id);
            $product->{$name} = $value;
            if ($name == 'purchase_price') {
                $product->margin = ($product->price - $value) * 100 / $product->price;
            }
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

    public function addSuplierLink(Request $request)
    {
        $link = ProductSuplierLink::firstOrCreate(array(
            'product_id' => $request->input('id'),
            'link' => $request->input('link'),
        ));
        return $link;
    }

    public function updateSuplierLink(Request $request)
    {
        if ($request->input('link') == '') {
            ProductSuplierLink::where('id', $request->input('link_id'))->delete();
            return 'deleted';
        }
        $link = ProductSuplierLink::updateOrCreate(array(
            'id' => $request->input('link_id'),
            'product_id' => $request->input('id'),
        ), array(
            'link' => $request->input('link'),
        ));
        return $link;
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
        $input = $request->except(array('orders_count', 'labels', 'supliers'));
        $link_ids = array();
        foreach ($input['suplierlinks'] as $link) {
            if (!$link['link']) continue;
            if (isset($link['id'])) {
                $suplier_link = ProductSuplierLink::find($link['id']);
                $suplier_link->update($link);
            } else {
                $suplier_link = ProductSuplierLink::create($link);
            }

            $link_ids[] = $suplier_link->id;
        }
        ProductSuplierLink::where('product_id', $product->id)->whereNotIn('id', $link_ids)->delete();
        unset($input['suplierlinks']);

        /*$price = $input['purchase_price'];
        $input['margin'] = ($input['price'] - $price) * 100 / $input['price'];*/
        $product->update($input);
        return $product;
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
