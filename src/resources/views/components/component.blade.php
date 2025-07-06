<div class="mermaid {{ $class ?? '' }}">
    @if(isset($data))
    {{ $data }}
    @else
        {{ \IcehouseVentures\LaravelMermaid\Support\Builder::setTheme($theme ?? '') }}
        {{ $slot }}
    @endif
</div>

