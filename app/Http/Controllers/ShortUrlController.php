<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

use App\Models\ShortUrls;
use App\Services\ShortUrl\ShortUrlCodeGenerator;
use App\Services\ShortUrl\ShortUrlQrCodeService;

class ShortUrlController extends Controller
{

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $shortUrls = ShortUrls::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('original_url', 'like', "%{$search}%")
                        ->orWhere('short_code', 'like', "%{$search}%")
                        ->orWhere('custom_slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('shortener.index', compact('shortUrls', 'search'));
    }

    public function create(): View
    {
        return view('shortener.create');
    }

    public function store(
        Request $request,
        ShortUrlCodeGenerator $codeGenerator,
        ShortUrlQrCodeService $qrCodeService
    ): RedirectResponse {
        $validated = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'original_url' => ['required', 'url'],
            'custom_slug'  => ['nullable', 'alpha_dash', 'min:3', 'max:100', 'unique:short_urls,custom_slug'],
            'is_active'    => ['nullable', 'boolean'],
            'expires_at'   => ['nullable', 'date', 'after:now'],
        ], [
            'original_url.required' => 'URL tujuan wajib diisi.',
            'original_url.url'      => 'URL tujuan harus berupa URL yang valid.',
            'custom_slug.alpha_dash'=> 'Custom slug hanya boleh berisi huruf, angka, dash, dan underscore.',
            'custom_slug.unique'    => 'Custom slug sudah digunakan.',
            'expires_at.after'      => 'Tanggal expired harus lebih dari waktu sekarang.',
        ]);

        $shortUrl = ShortUrls::create([
            'title'        => $validated['title'] ?? null,
            'original_url' => $validated['original_url'],
            'short_code'   => $codeGenerator->generate(),
            'custom_slug'  => $validated['custom_slug'] ?? null,
            'is_active'    => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : true,
            'expires_at'   => $validated['expires_at'] ?? null,
            'click_count'  => 0,
        ]);

        $qrPath = $qrCodeService->generate($shortUrl);

        $shortUrl->update([
            'qr_code_path' => $qrPath,
        ]);

        return redirect()
            ->route('shortener.index')
            ->with([
                'success'    => 'Short link berhasil dibuat.',
                'short_link' => $shortUrl->fresh()->public_url,
            ]);
    }

    public function show(ShortUrls $shortUrl): View
    {
        $recentVisits = $shortUrl->visits()
            ->latest()
            ->limit(20)
            ->get();

        $stats = [
            'total_clicks'    => (int) $shortUrl->click_count,
            'clicks_today'    => $shortUrl->visits()->whereDate('created_at', today())->count(),
            'clicks_7_days'   => $shortUrl->visits()->where('created_at', '>=', now()->subDays(7))->count(),
            'unique_visitors' => $shortUrl->visits()->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
            'last_visit_at'   => $shortUrl->visits()->latest('created_at')->value('created_at'),
        ];

        return view('shortener.show', compact('shortUrl', 'recentVisits', 'stats'));
    }

    public function edit(ShortUrls $shortUrl): View
    {
        return view('shortener.edit', compact('shortUrl'));
    }

    public function update(
        Request $request,
        ShortUrls $shortUrl,
        ShortUrlQrCodeService $qrCodeService
    ): RedirectResponse {
        $validated = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'original_url' => ['required', 'url'],
            'custom_slug'  => [
                'nullable',
                'alpha_dash',
                'min:3',
                'max:100',
                Rule::unique('short_urls', 'custom_slug')->ignore($shortUrl->id),
            ],
            'is_active'    => ['nullable', 'boolean'],
            'expires_at'   => ['nullable', 'date'],
        ], [
            'original_url.required' => 'URL tujuan wajib diisi.',
            'original_url.url'      => 'URL tujuan harus berupa URL yang valid.',
            'custom_slug.alpha_dash'=> 'Custom slug hanya boleh berisi huruf, angka, dash, dan underscore.',
            'custom_slug.unique'    => 'Custom slug sudah digunakan.',
        ]);

        if (!empty($validated['expires_at']) && now()->greaterThan($validated['expires_at'])) {
            return redirect()
                ->route('shortener.index')
                ->withErrors([
                    'expires_at' => 'Tanggal expired harus lebih dari waktu sekarang.',
                ])
                ->withInput();
        }

        $oldRouteCode = $shortUrl->route_code;

        $shortUrl->update([
            'title'        => $validated['title'] ?? null,
            'original_url' => $validated['original_url'],
            'custom_slug'  => $validated['custom_slug'] ?? null,
            'is_active'    => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : false,
            'expires_at'   => $validated['expires_at'] ?? null,
        ]);

        $shortUrl->refresh();

        $routeCodeChanged = $oldRouteCode !== $shortUrl->route_code;

        if ($routeCodeChanged || $shortUrl->wasChanged('original_url') || !$shortUrl->qr_code_path) {
            $qrPath = $qrCodeService->regenerate($shortUrl);

            $shortUrl->update([
                'qr_code_path' => $qrPath,
            ]);
        }

        return redirect()
            ->route('shortener.show', $shortUrl)
            ->with('success', 'Short link berhasil diperbarui.');
    }

    public function destroy(
        ShortUrls $shortUrl,
        ShortUrlQrCodeService $qrCodeService
    ): RedirectResponse {
        $qrCodeService->delete($shortUrl->qr_code_path);

        $shortUrl->delete();

        return redirect()
            ->route('shortener.index')
            ->with('success', 'Short link berhasil dihapus.');
    }

    // PUBLIC API
    public function shortUrl(Request $request, string $code)
    {
        $shortUrl = ShortUrls::query()
            ->where(function ($query) use ($code) {
                $query->where('short_code', $code)
                    ->orWhere('custom_slug', $code);
            })
            ->first();

        if (!$shortUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Short link not found.',
            ], 404);
        }

        if (!$shortUrl->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Short link is inactive.',
            ], 410);
        }

        if ($shortUrl->expires_at && now()->greaterThan($shortUrl->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Short link has expired.',
            ], 410);
        }

        // increment click count
        $shortUrl->increment('click_count');

        // create visit log
        $shortUrl->visits()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'source_app' => 'public-shortener',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $code,
                'original_url' => $shortUrl->original_url,
                'is_active' => $shortUrl->is_active,
                'expires_at' => optional($shortUrl->expires_at)?->toISOString(),
            ],
        ]);
    }

    public function shortUrlVisit(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string'],
            'referer' => ['nullable', 'string'],
        ]);

        $shortUrl = ShortUrls::query()
            ->where('short_code', $validated['code'])
            ->orWhere('custom_slug', $validated['code'])
            ->first();

        if (!$shortUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Short link not found.',
            ], 404);
        }

        $shortUrl->increment('click_count');

        $shortUrl->visits()->create([
            'ip_address' => $validated['ip_address'] ?? null,
            'user_agent' => $validated['user_agent'] ?? null,
            'referer' => $validated['referer'] ?? null,
            'source_app' => 'public-shortener',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function generateQRCode( Request $request, ShortUrlQrCodeService $qrCodeService) {

        $validated = $request->validate([
            'link' => ['required', 'url'],
        ], [
            'link.required' => 'Link is required.',
            'link.url' => 'Link must be a valid URL.',
        ]);
        
        // return response()->json($validated['link'], 200);

        $qr = $qrCodeService->overrideGenerate($validated['link']);

        return response($qr, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-code.svg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
