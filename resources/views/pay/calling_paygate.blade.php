<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>در حال اتصال به درگاه بانک ...</title>
    <style type="text/css">
        /* Preloader */
        .preloader {
            position: fixed;
            z-index: 9999;
            width: 100%;
            height: 100%;
            background-color: white;
        }
        .preloader img {
            position: absolute;
            top: calc(50% - 32px);
            left: calc(50% - 32px);
        }
        .preloader .main {
            position: absolute;
            top: calc(50% - 170px);
            left: calc(50% - 150px);
            background-color: rgba(0,0,0,0.3);
            width: 300px;
            height: 100px;
            text-align: center;
        }
        .preloader .main >p {
            color: whitesmoke;
            font-family: "Calibri";
            font-size: 22px;
        }
    </style>
    <script>
        function closethisasap() {
            document.forms["redirectpost"].submit();
        }
    </script>
</head>
<body onload="closethisasap();">
<div class="preloader">
    <img src="/img/loader.gif" alt="Preloader image">
    <div class="main">
        <p>درحال اتصال به درگاه بانک</p>
    </div>
</div>
<form name="redirectpost" method="post" action="{{$url}}">;
    @if(!is_null($data))
        @foreach ($data as $k => $v) {
        <input type="hidden" name="{{$k}}" value="{{$v}}">;
        @endforeach
    @endif
</form>
</body>
</html>
