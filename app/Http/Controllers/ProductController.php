<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductLabelp;
use App\ProductSuplier;
use App\Order;
use Illuminate\Support\Facades\DB;
use App\ProductSuplierLink;
use App\PromApi;
use App\Pack;
use App\ProductMonthOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
      $products = $products->with(['packitems','morders', 'labels', 'supliers', 'suplierlinks'])->orderBy('name')->paginate($per_page);

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

    public function getOrderMonth ($id) {
        //$pack = Pack::where('item_id', $id)->get()->pluck('product_id')->toArray();
        //$ids = array_merge($pack, array((int) $id));
        //$result = array();
        $months = DB::table('orders')
            ->join('order_products', 'orders.id', 'order_products.order_id')
                ->where('orders.status', 'delivered')
                ->where('order_products.product_id', $id)
                ->select(DB::raw('sum(order_products.quantity) as `qty`'), DB::raw('YEAR(orders.prom_date_created) year, MONTH(orders.prom_date_created) month'))
                ->groupBy('year','month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        /*foreach ($months as $month) {
          $result[$month->year.$month->month] = $month;
        }

        $pack_months = DB::table('orders')
            ->join('order_products', 'orders.id', 'order_products.order_id')
            ->join('packs', 'order_products.product_id', 'packs.product_id')
                ->where('orders.status', 'delivered')
                ->whereIn('order_products.product_id', $pack)
                ->select(DB::raw('sum(order_products.quantity*packs.koef) as `qty`'), DB::raw('YEAR(orders.prom_date_created) year, MONTH(orders.prom_date_created) month'))
                ->groupBy('year','month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        foreach ($pack_months as $month) {
          if (isset($result[$month->year.$month->month])) {
            $result[$month->year.$month->month]->qty += $month->qty;
          } else {
            $result[$month->year.$month->month] = $month;
          }
        }*/
        /*foreach ($months as $key => $month) {
          foreach ($pack_months as $pack_key => $pack_month) {
            if ($month->year == $pack_month->year &&
              $month->month == $pack_month->month) {
              $months[$key]->qty = $pack_month->qty;
            }
            if ($months->count() > $key + 1 &&
                $months[$key]->year < $pack_month->year &&
                $months[$key + 1]->year > $pack_month->year &&
                $months[$key]->month < $pack_month->month &&
                $months[$key + 1]->month > $pack_month->month
            ) {
             $months->splice($key, 0, [$pack_month]);
            }
          }
        }*/
        return $months;
    }

    public function importFromApiProcess (Request $request)
    {
        $last_id = $request->input('last_id');
        $api = new PromApi;
        $groups = DB::table('products')->groupBy('group_id')->select('group_id')->get()->pluck('group_id')->toArray();
        $prom_params = array('group_id' => '', 'limit' => 100);
        if ($last_id) {
            $prom_params['last_id'] = $last_id;
        }

        $prom_products = $api->getList('products', $prom_params)['products'];
        $total = Product::count();
        foreach ($prom_products as $prom_product) {
            $product = Product::updateOrCreate(array('prom_id' => $prom_product['id']),
              array(
                    'name' => $prom_product['name'],
                    'main_image' => $prom_product['main_image'],
                    'sku' => (string) $prom_product['sku'],
                    'status' => $prom_product['status'],
                    'presence' => $prom_product['presence'],
                    'group_id' => $prom_product['group']['id'],
                    'category' => $prom_product['group']['name'],
                    'price' => $prom_product['price'],
              ));
            if ($product->part_id == null) {
              $product->part_id = $prom_product['id'];
              $product->save();
            }
        }
        $ids = array_column($prom_products, 'id');
        if (count($ids) == 100) {
            $last_id = array_values(array_slice($ids, -1))[0];
            return array('last_id' => $last_id, 'imported' => count($prom_products), 'total' => $total);
        } else {
            return array('imported' => count($prom_products), 'total' => $total);
        }
    }

    public function importProcess (Request $request)
    {
        $start_row = $request->input('start_row');
        $result = array();
        if ($start_row == 0) {
            $start_row = 1;
            $request->file('importfile')->storeAs('csvfile', 'csvfile.csv');
            $path = storage_path('app/csvfile').'/csvfile.csv';
            $rows = 0;
            if(($handle = fopen($path, 'r')) !== false) {
                while(($data = fgetcsv($handle, 10000, ';')) !== false) {
                    $rows++;
                }
            }
            $result['total'] = $rows - 2;
        }
        //$filename = $request->file('csvfile')->getClientOriginalName();
        $path = storage_path('app/csvfile').'/csvfile.csv';
        $skip = 0;
        $imported = 0;
        $i = 0;
        $last_row = 0;
        if(($handle = fopen($path, 'r')) !== false) {
            while(($data = fgetcsv($handle, 10000, ';')) !== false && $i < 1000) {
                if ($skip <  $start_row) {$skip++; continue;}
                foreach( $data as &$value ) {
                  $value = iconv( 'cp1251','utf-8', $value );
                }
                $i++;
                //$data = explode(';', $data);
                $image = trim(explode(',', $data[9])[0]);

                Product::updateOrCreate(array('prom_id' => $data[18]), array(
                    'sku' => $data[0],
                    'name' => $data[1],
                    'price' => floatval(str_replace(',', '.', $data[3])),
                    'units' => $data[5],
                    'main_image' => $image,
                    //'min_balance' => intval($data[6]),
                    'presence' => ($data[10] == '+') ? 'available' : 'not_available',
                    'quantity' => intval($data[11]),
                    'group_id' => $data[12],
                    'category' => $data[13],
                    'subgroup_id' => $data[20],
                    'link' => trim($data[27]),
                ));
                $imported++;
            }
        }
        $last_row = $start_row + $i;
        $result['imported'] = $imported;
        $result['last_row'] = $last_row;
        return $result;
        //return array('imported' => $imported, 'last_row' => $last_row);
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
    public function show($id)
    {
      return Product::where('prom_id', $id)->first();
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
        $input = $request->except(array('packitems', 'orders', 'suplier_name', 'suplier_links', 'morders', 'orders_count', 'labels', 'supliers'));
        $link_ids = array();
        if (isset($input['suplierlinks'])) {
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
        }

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
    public function getSuplierProducts (Request $request)
    {
        $suplier_name = $request->input('suplier');

//'labels', 'supliers',
        $products = Product::with(['packitems', 'labels', 'supliers','morders','suplierlinks']);
        if ($request->has('on_display')) {
          $products = $products->where('status', 'on_display');
        }
        $products = $products->join('product_supliers', 'product_supliers.product_id', 'products.id')
            ->join('supliers', 'product_supliers.suplier_id', 'supliers.id')
           // ->select('products.*', DB::raw('IF( products.sort2 IS NOT NULL, products.sort2, 1000000) sort2'), 'supliers.name as suplier_name')
            ->select('products.*', 'supliers.name as suplier_name')
            ->orderBy('products.category');
        if ($request->has('sort2')) {
          $products = $products->orderByRaw('ISNULL(sort2), sort2 ASC');
        }
        if ($request->input('sort') == 'name') {
          $products = $products->orderBy('products.name');
        } elseif ($request->input('sort') == 'sku') {
          $products = $products->orderBy('products.sku');
        } else {
          $products = $products->orderBy('products.abc_earn')->orderBy('products.abc_qty');
        }

        $products = $products->where('supliers.name', 'like', '%'.$suplier_name.'%')->get();
        $categories = array();
        foreach ($products as $product) {
            /*$months = $this->getOrderMonth($product->id);
            foreach ($months as $month) {
                ProductMonthOrder::firstOrCreate(array(
                    'product_id' => $product->id,
                    'year' => $month->year,
                    'month' => $month->month,
                ),
                array(
                    'quantity' => $month->qty
                ));
            }*/
            if (!isset($categories[$product->category])) {
                $categories[$product->category] = array($product);
            } else {
                $categories[$product->category][] = $product;
            }
        }

        return $categories;
    }

    public function syncProductMonthOrders (Request $request)
    {
        $products = Product::paginate(50);
        foreach ($products as $product) {
            $months = $this->getOrderMonth($product->id);
            foreach ($months as $month) {
                ProductMonthOrder::updateOrCreate(array(
                    'product_id' => $product->id,
                    'year' => $month->year,
                    'month' => $month->month,
                ),
                array(
                    'quantity' => $month->qty
                ));
            }
        }
        return $products;
    }

    public function calcABC (Request $request)
    {

        $products = DB::table('products')
            ->join('order_products', 'order_products.product_id', 'products.id')
            ->join('order_statuses', 'order_products.order_id', 'order_statuses.order_id')
            ->join('orders', 'orders.id', 'order_products.order_id')
            ->whereDate('orders.prom_date_created', '>', '2018-09-01')
            ->whereDate('orders.prom_date_created', '<', '2018-12-01')
            ->groupBy('products.id')
            ->select('products.id', 'products.prom_id', DB::Raw('SUM((order_products.price - products.purchase_price)*order_products.quantity) as earn'))->orderBy('earn', 'desc')->get();
        $sum = $products->sum('earn');
        $agr = 0;
        $products = $products->map(function ($item) use ($sum, &$agr) {
            $agr += $item->earn * 100 / $sum;
            $abc = 'D';
            if ($agr < 50) {
                $abc = 'A';
            }
            if ($agr > 50 && $agr < 80) {
                $abc = 'B';
            }
            if ($agr > 80 && $agr < 95) {
                $abc = 'C';
            }
            Product::find($item->id)->update(array('abc_earn' => $abc));
            return ['id' => $item->prom_id, 'abc' => $abc, 'earn' => $item->earn, 'percent' => $item->earn * 100 / $sum, 'agr' => $agr];
        });
        return $products;
    }

    public function calcABCQty (Request $request)
    {

        $products = DB::table('products')
            ->join('order_products', 'order_products.product_id', 'products.id')
            ->join('order_statuses', 'order_products.order_id', 'order_statuses.order_id')
            ->join('orders', 'orders.id', 'order_products.order_id')
            ->whereDate('orders.prom_date_created', '>', '2018-09-01')
            ->whereDate('orders.prom_date_created', '<', '2018-12-01')
            ->groupBy('products.id')
            ->select('products.id', 'products.prom_id', DB::Raw('SUM(order_products.quantity) as qty'))->orderBy('qty', 'desc')->get();
        $sum = $products->sum('qty');
        $agr = 0;
        $products = $products->map(function ($item) use ($sum, &$agr) {
            $agr += $item->qty * 100 / $sum;
            $abc = 'D';
            if ($agr < 50) {
                $abc = 'A';
            }
            if ($agr > 50 && $agr < 80) {
                $abc = 'B';
            }
            if ($agr > 80 && $agr < 95) {
                $abc = 'C';
            }
            Product::find($item->id)->update(array('abc_qty' => $abc));
            return ['id' => $item->prom_id, 'abc' => $abc, 'qty' => $item->qty, 'percent' => $item->qty * 100 / $sum, 'agr' => $agr];
        });
        return $products;
    }

    public function dashboardStats ()
    {
      $sums = array(
        'qty' => Product::where('status', 'on_display')->count(),
        'sklad' => Product::where('status', 'on_display')->sum('quantity'),
        'purchase' => DB::table('products')->where('status', 'on_display')->select(DB::Raw('SUM(products.purchase_price * products.quantity) as purchase'))->first()->purchase,
        'price' => DB::table('products')->where('status', 'on_display')->select(DB::Raw('SUM(products.price * products.quantity) as price'))->first()->price
      );
      $stat_abc = DB::table('products')->where('status', 'on_display')->select(
          'abc_earn',
          'abc_qty',
          DB::Raw('COUNT(products.id) as qty'),
          DB::Raw('SUM(products.quantity) as sklad'),
          DB::Raw('SUM(products.purchase_price * products.quantity) as purchase'),
          DB::Raw('SUM(products.price * products.quantity) as price')
      )->groupBy('abc_earn')->groupBy('abc_qty')->get();
      /*$qty_abc = $qty_abc->map(function ($item) use ($sums) {
        return array(
          'qty' => $item->qty * 100 / $sums['qty'],
          'sklad' => $item->sklad * 100/ $sums['sklad'],
          'purchase' => $item->purchase * 100/ $sums['purchase'],
          'price' => $item->price * 100 / $sums['price'],
        );
      });*/

      return array('sums' => $sums, 'stat_abc' => $stat_abc);
    }

}
