$('#import_orders').click(function () {
  importOrdersData(1)
})

$('#import_products').click(function () {
  importProductsData(1)
})

$('#import_order_products').click(function () {
  importOrderProducts(8301)
})

var imported = {
   'customer': 0,
   'product': 0,
   'phone': 0,
   'email': 0,
   'order': 0,
}

function importOrderProducts (start_row) {
  var params = {'start_row': start_row}
  $.get('/api/importorderproducts', params, function(res) {
    console.log(start_row + 1*res)
    if(res == 50) {
      importOrderProducts (1*start_row + 1*res)
    }
  })
}

function importProductsData (start_row) {
  var params = {'start_row': start_row}
  $('.imported .product').text(imported.product)
  $.get('/api/importproducts', params, function(res) {
    imported.product += res.product
    console.log(res)
    //console.log(res.end_row, res.start_row)
    if (res.end_row != res.start_row) {
      importProductsData(res.end_row)
    }
  })
}

function importOrdersData (start_row, order_id) {
  var params = {'start_row': start_row}
  if (typeof(order_id) != 'undefined') {
    params['order_id'] = order_id
  }
  console.log(order_id)
  for (var name in imported) {
    $('.imported .'+name).text(imported[name])
    $('.processed').text(start_row)
  }
  $.get('/api/import', params, function(res) {
    console.log(res)
    for (var name in imported) {
      imported[name]+= res[name]
    }
    //console.log(res.end_row, res.start_row)
    if (res.end_row != res.start_row) {
      importData(res.end_row, res.order_id)
    }
  })
}
