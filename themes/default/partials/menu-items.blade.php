@foreach($items as $item)
  @php $children = $item['children'] ?? []; @endphp
  <div class="nav-item {{ $children ? 'has-submenu' : '' }}">
    <a href="{{ $item['url'] }}" target="{{ $item['target'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
      <span>{{ $item['label'] }}</span>
    </a>

    @if($children)
      <button class="submenu-toggle" type="button" aria-label="Toggle {{ $item['label'] }} submenu" aria-expanded="false">⌄</button>
      <div class="nav-submenu">
        @include('themes.default::partials.menu-items', ['items' => $children])
      </div>
    @endif
  </div>
@endforeach
