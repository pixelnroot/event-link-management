{{--
    Component: Link Form Modal (Alpine.js powered)
    Shows total links for event + checks for duplicate URLs.
--}}

<div x-show="isOpen"
     x-cloak
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    {{-- Modal Content --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         @click.stop
         class="relative w-full max-w-lg rounded-2xl border border-surface-300/50 bg-surface-50 shadow-2xl shadow-black/40">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-surface-300/30 px-6 py-5">
            <div>
                <h2 class="font-display text-xl font-700 text-ink" x-text="eventName">Event</h2>
                <p class="text-sm text-ink-secondary mt-0.5">Add links to this event</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Total links badge --}}
                <div class="flex items-center gap-1.5 rounded-lg border border-surface-300 bg-surface-200 px-3 py-1.5">
                    <svg class="h-3.5 w-3.5 text-lime" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span class="text-xs font-semibold text-ink" x-text="existingLinks.length + ' links'"></span>
                </div>

                <button @click="closeModal()"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-surface-300 bg-surface-200 text-ink-secondary transition hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <form :action="'/events/' + eventId + '/links'" method="POST" @submit.prevent="submitForm($event)" class="px-6 py-6 space-y-6">
            @csrf

            {{-- Category Select --}}
            <div class="space-y-2">
                <label for="category_id" class="block text-sm font-medium text-ink-secondary">
                    Select Category
                </label>
                <div class="relative">
                    <select name="category_id"
                            id="category_id"
                            x-model="selectedCategory"
                            required
                            class="w-full appearance-none rounded-xl border border-surface-300 bg-surface-200 px-4 py-3 pr-10 text-sm text-ink outline-none transition focus:border-lime/40 focus:ring-2 focus:ring-lime/10 hover:border-surface-300/80">
                        <option value="" disabled selected class="text-ink-muted">Choose a category...</option>
                        <template x-for="cat in eventCategories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                    <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <p x-show="eventCategories.length === 0" class="text-xs text-amber-400 mt-1">
                    This event has no categories yet. Please add categories in the admin panel first.
                </p>
            </div>

            {{-- Dynamic Links (URL only) --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-ink-secondary">Links</label>
                    <button type="button"
                            @click="addLink()"
                            class="flex items-center gap-1.5 rounded-lg border border-lime/20 bg-lime/5 px-3 py-1.5 text-xs font-medium text-lime transition hover:bg-lime/10 hover:border-lime/30">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Link
                    </button>
                </div>

                {{-- Links list --}}
                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    <template x-for="(link, index) in links" :key="index">
                        <div class="group/link animate-fade-up">
                            <div class="flex items-center gap-3">
                                {{-- URL Input --}}
                                <div class="relative flex-1">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2"
                                         :class="isDuplicate(link.url) ? 'text-red-400' : 'text-ink-muted'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <input type="url"
                                           :name="'links[' + index + '][url]'"
                                           x-model="link.url"
                                           @input="link.url = link.url.trim()"
                                           placeholder="https://example.com"
                                           required
                                           :class="isDuplicate(link.url) ? 'border-red-500/50 focus:border-red-500/60 focus:ring-red-500/10' : 'border-surface-300 focus:border-lime/40 focus:ring-lime/10'"
                                           class="w-full rounded-xl bg-surface-200 py-2.5 pl-10 pr-4 text-sm text-ink placeholder-ink-muted outline-none transition focus:ring-2">
                                </div>

                                {{-- Remove button --}}
                                <button type="button"
                                        @click="removeLink(index)"
                                        x-show="links.length > 1"
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-surface-300 bg-surface-200 text-ink-muted opacity-0 transition group-hover/link:opacity-100 hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Duplicate warning --}}
                            <p x-show="isDuplicate(link.url)"
                               x-transition
                               class="mt-1.5 ml-1 flex items-center gap-1.5 text-xs text-red-400">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                This link already exists in this event
                            </p>
                        </div>
                    </template>
                </div>

                {{-- Empty state --}}
                <div x-show="links.length === 0"
                     class="flex flex-col items-center justify-center rounded-xl border border-dashed border-surface-300 bg-surface-200/50 py-8 text-center">
                    <svg class="h-8 w-8 text-ink-muted mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <p class="text-sm text-ink-muted">No links added yet</p>
                    <button type="button" @click="addLink()" class="mt-2 text-xs text-lime hover:underline">Add your first link</button>
                </div>
            </div>

            {{-- Duplicate summary warning --}}
            <div x-show="hasDuplicates()"
                 x-transition
                 class="rounded-xl border border-red-500/20 bg-red-500/5 px-4 py-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p class="text-sm text-red-400">Please remove duplicate links before saving.</p>
            </div>

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-500/20 bg-red-500/5 p-4">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-sm text-red-400 flex items-center gap-2">
                                <span class="h-1 w-1 rounded-full bg-red-400 flex-shrink-0"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-surface-300/30">
                <button type="button"
                        @click="closeModal()"
                        class="rounded-xl border border-surface-300 bg-surface-200 px-5 py-2.5 text-sm font-medium text-ink-secondary transition hover:border-surface-300/80 hover:text-ink">
                    Cancel
                </button>
                <button type="submit"
                        :disabled="!selectedCategory || links.length === 0 || hasDuplicates()"
                        :class="(!selectedCategory || links.length === 0 || hasDuplicates()) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-lime-dim hover:shadow-lg hover:shadow-lime/10'"
                        class="rounded-xl bg-lime px-6 py-2.5 text-sm font-semibold text-surface transition-all duration-200">
                    Save Links
                </button>
            </div>
        </form>
    </div>
</div>
