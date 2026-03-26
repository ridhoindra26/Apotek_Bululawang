<?php

namespace App\Services\ShortUrl;

use App\Models\ShortUrls;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ShortUrlQrCodeService
{
    public function generate(ShortUrls $shortUrl): string
    {
        $directory = 'qrcodes/short-links';
        $filename  = $shortUrl->id . '.svg';
        $path      = $directory . '/' . $filename;

        Storage::disk('public')->makeDirectory($directory);

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($shortUrl->public_url);

        $qrSvgWithLogo = $this->insertLogoIntoSvg($qrSvg);

        Storage::disk('public')->put($path, $qrSvgWithLogo);

        return $path;
    }

    public function regenerate(ShortUrls $shortUrl): string
    {
        if ($shortUrl->qr_code_path && Storage::disk('public')->exists($shortUrl->qr_code_path)) {
            Storage::disk('public')->delete($shortUrl->qr_code_path);
        }

        return $this->generate($shortUrl);
    }

    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function insertLogoIntoSvg(string $svg): string
    {
        $logoPath = public_path('assets/img/logo.png');

        if (!file_exists($logoPath)) {
            return $svg;
        }

        $logoContents = file_get_contents($logoPath);
        if ($logoContents === false) {
            return $svg;
        }

        $logoBase64 = base64_encode($logoContents);
        $logoMime   = mime_content_type($logoPath) ?: 'image/png';

        preg_match('/viewBox="([\d.\s]+)"/', $svg, $matches);

        if (!isset($matches[1])) {
            return $svg;
        }

        [$minX, $minY, $width, $height] = array_map('floatval', preg_split('/\s+/', trim($matches[1])));

        $logoSize   = $width * 0.20;   // 20% dari QR
        $logoX      = $minX + (($width - $logoSize) / 2);
        $logoY      = $minY + (($height - $logoSize) / 2);
        $bgPadding  = $logoSize * 0.18;

        $backgroundX      = $logoX - $bgPadding;
        $backgroundY      = $logoY - $bgPadding;
        $backgroundSize   = $logoSize + ($bgPadding * 2);
        $backgroundRadius = $backgroundSize * 0.16;

        $logoSvg = '
    <rect
        x="' . $backgroundX . '"
        y="' . $backgroundY . '"
        width="' . $backgroundSize . '"
        height="' . $backgroundSize . '"
        rx="' . $backgroundRadius . '"
        ry="' . $backgroundRadius . '"
        fill="white"
    />
    <image
        x="' . $logoX . '"
        y="' . $logoY . '"
        width="' . $logoSize . '"
        height="' . $logoSize . '"
        href="data:' . $logoMime . ';base64,' . $logoBase64 . '"
        preserveAspectRatio="xMidYMid meet"
    />
</svg>';

        return preg_replace('/<\/svg>\s*$/', $logoSvg, $svg) ?: $svg;
    }
}