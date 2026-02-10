{{--
    Component: Event Card
    Usage: @include('components.event-card', ['event' => $event])
    Schema: events(id, name, slug, timestamps)
--}}

@props(['event'])

<div class="animate-fade-up group relative cursor-pointer"
     @click="openModal(
         {{ $event->id }},
         '{{ addslashes($event->name) }}',
         {{ $event->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toJson() }},
         {{ $event->categories->flatMap(fn($c) => $c->links->pluck('url'))->values()->toJson() }}
     )">

    {{-- Card --}}
    <div class="relative overflow-hidden rounded-2xl border border-surface-300/50 bg-surface-50 p-6 transition-all duration-300 hover:border-lime/20 hover:bg-surface-100 hover:shadow-xl hover:shadow-lime/5">

        {{-- Hover glow effect --}}
        <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full opacity-0 transition-opacity duration-500 group-hover:opacity-100 glow-dot blur-2xl"></div>

        {{-- Top row: Icon + Name --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-lime/10 border border-lime/20">
                    <svg class="h-5 w-5 text-lime" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-lg font-600 text-ink leading-snug group-hover:text-lime transition-colors duration-300">
                        {{ $event->name }}
                    </h3>
                    <p class="text-xs text-ink-muted mt-0.5">
                        {{ $event->slug }}
                    </p>
                </div>
            </div>

            <span class="text-xs text-ink-muted">
                {{ $event->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Categories tags --}}
        @if($event->categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($event->categories as $category)
                    <span class="inline-flex items-center gap-1 rounded-lg border border-lime/15 bg-lime/5 px-2.5 py-1 text-xs font-medium text-lime/80">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $category->name }}
                        <span class="text-ink-muted">({{ $category->links->count() }})</span>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Bottom row --}}
        <div class="flex items-center justify-between pt-3 border-t border-surface-300/30">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-xs text-ink-muted">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ $event->categories->count() }} categories
                </span>
                <span class="flex items-center gap-1.5 text-xs text-ink-muted">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    {{ $event->categories->sum(fn($c) => $c->links->count()) }} links
                </span>
            </div>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-200 transition-all duration-300 group-hover:bg-lime group-hover:text-surface">
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
    </div>
</div>
