<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Оставить комментарий</title>

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
<main>
@if (isset($repeat))
    <h1>Уже проголосовали</h1>
@else
@if (isset($vote))
    <h1>Спасибо за вашу оценку  - {{$vote}}! </h1>
    <h4>
        @if ($vote > 8)
        Какие Ваши пожелания по улучшению работы нашей компании?
        @endif
        @if ($vote > 6 and $vote < 9)
        Что бы Вы хотели улучшить в работе нашей компании? <br />
        Напишите любые свои предложения:
        @endif
        @if ($vote < 7)
        Опишите, пожалуйста, Вашу ситуацию. <br />
        Мы обязательно разберемся в ней и примем соответствующие меры.
        @endif
    </h4>
    <form action="{{url('/')}}" method="post"  >
        {{ csrf_field() }}
        <input type="hidden" value="{{$hash}}" name="hash" />
        <input type="hidden" value="{{$vote}}" name="vote" />
        <textarea id="comment" name="comment" placeholder="Оставить комментарий"></textarea>
        <button type="submit" id="submit">Отправить</button>
    </form>

    <script
      src="https://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous"></script>
    <script src="{{ asset('js/votes.js') }}"></script>
@endif
@endif
</main>
<div class="copyraite">
    2014–2019 Helgamade.com.ua
</div>
    </body>
</html>
