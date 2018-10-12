$('#import').click(function () {
  importData(31500)
})

var imported = {
   'customer': 0,
   'product': 0,
   'phone': 0,
   'email': 0,
   'order': 0,
}

function importData (start_row, order_id) {
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
