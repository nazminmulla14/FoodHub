
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'FoodHub - Food Delivery' }}</title>

    @include('partials.styles')

    @livewireStyles
</head>

<body @class(['page-inner' => ! request()->routeIs('home')])>
    @include('partials.spinner')
    
    @include('partials.navbar')

    <main>
        {{ $slot }}
    </main>

    @include('partials.footer')

    @include('partials.scripts')

    @livewireScripts

</body>
</html>