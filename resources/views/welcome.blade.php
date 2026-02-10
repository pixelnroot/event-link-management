{{--
    Page: Events Index
    Route: GET /
    Controller passes: $events (paginated), $totalLinks
--}}

@extends('layouts.app')

@section('title', 'Events')

@section('content')

    <div x-data="eventModal()" x-cloak>

        {{-- Page Header --}}
        <div class="mb-10 animate-fade-up">
            <div class="flex items-center gap-2 mb-4">
                <div class="h-px w-8 bg-lime/50"></div>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-lime">Discover</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="font-display text-4xl sm:text-5xl font-900 tracking-tight text-ink leading-none">
                        Events
                    </h1>
                    <p class="mt-3 text-base text-ink-secondary max-w-lg leading-relaxed">
                        Browse events and attach categorized links to each one.
                    </p>
                </div>

                <form method="GET" action="{{ route('events.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search events..."
                               class="w-56 rounded-xl border border-surface-300 bg-surface-50 py-2.5 pl-10 pr-4 text-sm text-ink placeholder-ink-muted outline-none transition focus:border-lime/40 focus:ring-2 focus:ring-lime/10">
                    </div>
                    <button type="submit"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-surface-300 bg-surface-50 text-ink-secondary transition hover:border-lime/30 hover:text-lime">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="mb-8 grid grid-cols-3 gap-4 animate-fade-up" style="animation-delay: 0.1s">
            <div class="rounded-xl border border-surface-300/50 bg-surface-50 px-5 py-4">
                <p class="text-2xl font-display font-700 text-ink">{{ $events->total() }}</p>
                <p class="text-xs text-ink-muted mt-1">Total Events</p>
            </div>
            <div class="rounded-xl border border-surface-300/50 bg-surface-50 px-5 py-4">
                <p class="text-2xl font-display font-700 text-lime">{{ $events->sum(fn($e) => $e->categories->count()) }}</p>
                <p class="text-xs text-ink-muted mt-1">Categories</p>
            </div>
            <div class="rounded-xl border border-surface-300/50 bg-surface-50 px-5 py-4">
                <p class="text-2xl font-display font-700 text-ink">{{ $totalLinks ?? 0 }}</p>
                <p class="text-xs text-ink-muted mt-1">Total Links</p>
            </div>
        </div>

        {{-- Event List --}}
        @if($events->count() > 0)
            <div class="stagger space-y-4">
                @foreach($events as $event)
                    @include('components.event-card', ['event' => $event])
                @endforeach
            </div>

            <div class="mt-10">
                {{ $events->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-surface-300 bg-surface-50/50 py-20 text-center animate-fade-up">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-surface-200 border border-surface-300 mb-4">
                    <svg class="h-7 w-7 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-600 text-ink">No events found</h3>
                <p class="mt-1 text-sm text-ink-muted max-w-xs">
                    @if(request('search'))
                        No events match "{{ request('search') }}". Try a different search term.
                    @else
                        Events will appear here once they are created.
                    @endif
                </p>
            </div>
        @endif

        @include('components.link-form-modal')

    </div>

@endsection

@push('scripts')
<script>
    function eventModal() {
        return {
            isOpen: false,
            eventId: null,
            eventName: '',
            eventCategories: [],
            existingLinks: [],
            selectedCategory: '',
            links: [{ url: '' }],

            openModal(id, name, categories, existingLinks) {
                this.eventId = id;
                this.eventName = name;
                this.eventCategories = categories;
                this.existingLinks = existingLinks || [];
                this.selectedCategory = '';
                this.links = [{ url: '' }];
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeModal() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },

            addLink() {
                this.links.push({ url: '' });
            },

            removeLink(index) {
                this.links.splice(index, 1);
            },

            /**
             * Check if a URL is duplicate:
             * 1. Against existing links in the event (from DB)
             * 2. Against other new links being added in the form
             */
            isDuplicate(url) {
                if (!url || url.trim() === '') return false;

                const normalized = url.trim().toLowerCase().replace(/\/+$/, '');

                // Check against existing event links
                const existsInDb = this.existingLinks.some(
                    existing => existing.trim().toLowerCase().replace(/\/+$/, '') === normalized
                );
                if (existsInDb) return true;

                // Check for duplicates within the current form inputs
                const allNewUrls = this.links
                    .map(l => l.url.trim().toLowerCase().replace(/\/+$/, ''))
                    .filter(u => u !== '');

                const count = allNewUrls.filter(u => u === normalized).length;
                return count > 1;
            },

            /**
             * Check if any link in the form has duplicates
             */
            hasDuplicates() {
                return this.links.some(link => this.isDuplicate(link.url));
            },

            /**
             * Submit with frontend validation
             */
            submitForm(event) {
                if (this.hasDuplicates()) return;

                event.target.submit();
            },
        }
    }
</script>
@endpush
