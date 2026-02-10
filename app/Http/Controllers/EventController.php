<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Link;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()
            ->with('categories.links')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        $totalLinks = Link::count();

        return view('welcome', compact('events', 'totalLinks'));
    }

    /**
     * Store links for an event's category.
     */
    public function storeLinks(Request $request, Event $event)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'links' => 'required|array|min:1',
            'links.*.url' => 'required|url|max:2048',
        ]);

        // Ensure category belongs to this event
        $category = $event->categories()->findOrFail($request->category_id);

        // Get all existing URLs across all categories of this event
        $existingUrls = $event->categories()
            ->with('links')
            ->get()
            ->flatMap(fn ($cat) => $cat->links->pluck('url'))
            ->map(fn ($url) => rtrim(strtolower(trim($url)), '/'))
            ->toArray();

        // Collect new URLs and check for duplicates
        $newUrls = [];
        $duplicates = [];

        foreach ($request->links as $linkData) {
            $normalized = rtrim(strtolower(trim($linkData['url'])), '/');

            // Check against existing DB links
            if (in_array($normalized, $existingUrls)) {
                $duplicates[] = $linkData['url'];

                continue;
            }

            // Check against other new links in same request
            if (in_array($normalized, $newUrls)) {
                $duplicates[] = $linkData['url'];

                continue;
            }

            $newUrls[] = $normalized;
        }

        if (! empty($duplicates)) {
            return back()
                ->withInput()
                ->withErrors(['links' => 'Duplicate links found: '.implode(', ', $duplicates)]);
        }

        // All clear — create the links
        foreach ($request->links as $linkData) {
            $category->links()->create([
                'url' => $linkData['url'],
            ]);
        }

        return back()->with('success', count($request->links).' link(s) added to "'.$event->name.'" successfully!');
    }
}
