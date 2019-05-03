$('.remote-vote-btns a, .remote-link').click(function (e) {
  e.preventDefault()
  var order_id = $('main').attr('data-id')
  var href = $(this).attr('href')
  $.get('/remoteclick', {'id': order_id}, function (res) {
    window.open(href, '_blank');
  })
})
/*$('form').submit(function(e) {
  if ($('.remote-vote-btns').length > 0) {
    e.preventDefault()
    var form = $(this);
    var url = form.attr('action')
    //$.ajax({type: "POST", url: url, data: form.serialize() + '&ajax=1', function(res) { console.log(res) }})
    $('.remote-vote-btns').addClass('active')
  }
})*/
