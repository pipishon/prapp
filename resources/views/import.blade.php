<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
<div class="container">
  <div class="row">
    <div class="col-6">
      <h1> Import  </h1>
      <form action="/importupload" method="post" enctype="multipart/form-data">
        <div class="form-group">
<label for="csv_file"> Csv file </label>
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="file" id="csv_file" name="csvfile">
          <button type="submit" class="btn btn-primary float-right">Upload</button>
        </div>

      </form>
    </div>
  </div>
  @if ( $uploaded_file_name !=  '')
  <div class="row">
    <div class="jumbotron col-6">
      <span>{{$uploaded_file_name}}</span> <button id="import_orders" class="btn btn-primary float-right">Start import orders</button>
      <span>{{$uploaded_file_name}}</span> <button id="import_products" class="btn btn-primary float-right">Start import products</button>
      <span>{{$uploaded_file_name}}</span> <button id="import_order_products" class="btn btn-primary float-right">Start import order products</button>
    </div>
    <div class="jumbotron col-6 imported">
      <div >Processed <span class="processed">0</span>/{{$lines}}</div>
      <table class="table">
        <tr><td>customers</td><td class="customer">0</td> </tr>
        <tr><td>products</td><td class="product">0</td> </tr>
        <tr><td>phones</td><td class="phone">0</td> </tr>
        <tr><td>emails</td><td class="email">0</td> </tr>
        <tr><td>orders</td><td class="order">0</td> </tr>
      </table>
    </div>
  </div>
  @endif
</div>
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<script src="{{ asset('js/import.js') }}"></script>
    </body>
</html>
