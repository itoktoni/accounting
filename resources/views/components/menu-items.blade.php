@props(['items', 'mobile' => false])

@php
    $menu = config('menu.sidebar');
    $isActiveItem = function ($item) {
        $routeName = $item['route'];
        $matchRoutes = $item['match'] ?? [];

        return request()->routeIs($routeName)
            || request()->routeIs($routeName.'.*')
            || collect($matchRoutes)->contains(fn ($m) => request()->routeIs($m));
    };
@endphp

@foreach($menu as $section)
    @if($section['label'])
        <div class="px-4 pt-5 pb-1.5 text-[11px] font-semibold text-on-surface-variant/70 uppercase tracking-[0.12em]">{{ $section['label'] }}</div>
    @endif
    @foreach($section['items'] as $item)
        @if(!empty($item['children']))
            @php $openChild = collect($item['children'])->contains(fn ($c) => $isActiveItem($c)); @endphp
            <details class="group" @if($openChild) open @endif>
                <summary class="flex items-center gap-3 px-4 py-2.5 cursor-pointer rounded-xl text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors list-none [&::-webkit-details-marker]:hidden {{ $openChild ? 'text-on-surface font-medium' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                    <span class="text-sm flex-1">{{ $item['label'] }}</span>
                    <span class="material-symbols-outlined text-[18px] transition-transform duration-200 group-open:rotate-180">expand_more</span>
                </summary>
                <div class="relative ml-[27px] my-0.5 pl-3 border-l border-outline-variant/70 space-y-0.5">
                    @foreach($item['children'] as $child)
                        @php $active = $isActiveItem($child); @endphp
                        <a
                            href="{{ route($child['route']) }}"
                            wire:navigate
                            @if($mobile) @click="drawerOpen = false" @endif
                            class="block px-3 py-2 rounded-lg text-[13px] transition-colors {{ $active ? 'bg-primary text-on-primary font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                </div>
            </details>
        @else
            @php $active = $isActiveItem($item); @endphp
            <a
                href="{{ route($item['route']) }}"
                wire:navigate
                @if($mobile) @click="drawerOpen = false" @endif
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors {{ $active ? 'bg-primary text-on-primary font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
            >
                <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                <span class="text-sm">{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
@endforeach
