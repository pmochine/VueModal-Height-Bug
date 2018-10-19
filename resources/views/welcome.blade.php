<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        
    </head>
    <body style="background: orange;">
        <div id="app" style="display: flex; justify-content: center; align-items: center;">
            <example-component></example-component>

            <button @click="$modal.show('hello-world')" style="padding: 50px;">Open Modal</button>

            <button @click="$modal.show('hellohello')" style="padding: 50px;">Open Modal 2</button>
        </div>

        <script src="/js/app.js"></script>
    </body>
</html>
