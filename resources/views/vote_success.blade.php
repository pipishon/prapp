<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cпасибо</title>

        <link href="{{ asset('css/votes.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
<header>
<div class="logo">
    <a href="http://{{env('APP_BASE_DOMAIN')}}" />
        <img src="/imgs/logo.png" />
    </a>
</div>
<div class="message">
Магазин №1 Фетра и Фоамирана в Украине
</div>
</header>
@if (session()->has('vote'))
<main data-id="{{session()->get('order_id')}}">
    @if (session()->get('vote') > 8)
        <h1>Спасибо за Ваши пожелания 🌸<br />
        Мы ценим, что вы нашли время поделиться <br />
        своими мыслями!</h1>
        <h4>Будем очень признательны, если вы уделите ещё минуту своего <br />
            времени и оставите отзыв о нашем магазине на площадках <br />
            <a target="_blank" class="remote-link" style="font-size: 16px;" href="http://kiev.prom.ua/opinions/list/2054335">Prom.ua</a>&nbsp; и&nbsp; <a target="_blank" class="remote-link" style="font-size: 16px;" href="https://search.google.com/local/writereview?placeid=ChIJy5LAnKbF1EARJ_CkshyRihY">Карты Google</a>
        </h4>
        <p>Оставить отзывы можно здесь:</p>
        <div class="remote-vote-btns">
            <a target="_blank" href="http://kiev.prom.ua/opinions/list/2054335"><img src="/imgs/prom_btn.png" /></a><a target="_blank" href="https://search.google.com/local/writereview?placeid=ChIJy5LAnKbF1EARJ_CkshyRihY"><img src="/imgs/google_btn.png" /></a>
        </div>
    @endif

    @if (session()->get('vote') < 9 and session()->get('vote') > 6)
        <h1>Спасибо за ваш отзыв!<br />
        Мы благодарны вам за то, что вы являетесь  <br />
        нашим клиентом!</h1>
        <h4>
            Уже не можем дождаться, чтобы изучить ваш ответ.<br />
            Мы учтем ваши пожелания в дальнейшей нашей работе.
        </h4>
        <p>Будем признательны за ваш отзыв на:</p>
        <div class="remote-vote-btns">
            <a target="_blank" href="http://kiev.prom.ua/opinions/list/2054335"><img src="/imgs/prom_btn.png" /></a><a target="_blank" href="https://search.google.com/local/writereview?placeid=ChIJy5LAnKbF1EARJ_CkshyRihY"><img src="/imgs/google_btn.png" /></a>
        </div>
    @endif

    @if (session()->get('vote') < 7)
        <h1>
            Мы ценим ваше время!<br />
            Нам очень жаль, что мы не оправдали ваши ожидания :(
        </h1>
        <h4>
            Мы обязательно разберемся в сложившейся ситуации и примем<br />
            необходимые меры, чтобы подобного не повторилось впредь.
        </h4>
        <h4>
            Всегда рады Вашим заказам.<br />
            Спасибо, что выбрали нас!
        </h4>
        <a href="http://{{env('APP_BASE_DOMAIN')}}">Перейти на сайт компании</a>
    @endif
</main>
<div class="copyraite">
    2014–2019 Helgamade.com.ua
</div>
    <script
      src="https://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous"></script>
    <script src="{{ asset('js/votes.js') }}"></script>
@endif
    </body>
</html>
