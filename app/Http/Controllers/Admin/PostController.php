<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('author')
                    ->latest()
                    ->paginate(10);

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string|max:255',
        ]);

        // Generate slug from title
        $validated['slug'] = $this->generateSlug($request->title);
        
        // Set author
        $validated['author_id'] = Auth::id();

        // HAPUS SEMUA LOGIC SCHEDULING
        $validated['schedule_type'] = 'immediate'; // Selalu immediate
        $validated['publish_start'] = null;
        $validated['publish_end'] = null;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
            $validated['image'] = $imagePath;
        }

        // Handle tags
        if ($request->tags) {
            $validated['tags'] = $this->formatTags($request->tags);
        }

        $post = Post::create($validated);

        return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Postingan berhasil dibuat!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string|max:255',
        ]);

        // Generate new slug if title changed
        if ($post->title !== $request->title) {
            $validated['slug'] = $this->generateSlug($request->title);
        }

        // HAPUS SEMUA LOGIC SCHEDULING
        $validated['schedule_type'] = 'immediate';
        $validated['publish_start'] = null;
        $validated['publish_end'] = null;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            
            $imagePath = $request->file('image')->store('posts', 'public');
            $validated['image'] = $imagePath;
        }

        // Handle tags
        if ($request->tags) {
            $validated['tags'] = $this->formatTags($request->tags);
        }

        $post->update($validated);

        return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Postingan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Delete image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Postingan berhasil dihapus!');
    }

    /**
     * Toggle post status manually
     */
    public function toggleStatus(Post $post)
    {
        $post->status = $post->status === 'published' ? 'draft' : 'published';
        $post->save();

        $action = $post->status === 'published' ? 'diterbitkan' : 'disimpan sebagai draft';

        return redirect()
                ->route('admin.posts.index')
                ->with('success', "Postingan berhasil {$action}!");
    }

    /**
     * Generate unique slug
     */
    private function generateSlug($title)
    {
        $slug = Str::slug($title);
        $count = Post::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Format tags from string to array
     */
    private function formatTags($tags)
    {
        $tagsArray = explode(',', $tags);
        $formattedTags = array_map(function($tag) {
            return trim($tag);
        }, $tagsArray);
        
        return implode(',', $formattedTags);
    }
}