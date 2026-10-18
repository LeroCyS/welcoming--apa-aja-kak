<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Foto & lagu di chat. Foto selalu di-encode ulang (membuang EXIF/lokasi) dan disimpan di disk private. */
class ChatMedia
{
    public const MAX_DIMENSION = 1600;

    /** Batas piksel sebelum foto dibuka GD (±40 MP), agar PNG kecil berukuran raksasa tidak menghabiskan memori. */
    public const MAX_SOURCE_PIXELS = 40_000_000;

    /**
     * Encode ulang foto menjadi JPEG tanpa metadata, perkecil bila terlalu besar.
     *
     * @return array{path: string, width: int, height: int, size: int}
     */
    public function storeImage(UploadedFile $file, string $folder, string $disk = 'local'): array
    {
        $size = @getimagesize($file->getRealPath());
        if (! $size || $size[0] < 1 || $size[1] < 1) {
            throw ValidationException::withMessages(['image' => 'Foto tidak bisa dibaca. Coba foto lain, ya.']);
        }
        if ($size[0] * $size[1] > self::MAX_SOURCE_PIXELS) {
            throw ValidationException::withMessages(['image' => 'Resolusi foto terlalu besar. Coba foto lain, ya.']);
        }
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if ($source === false) {
            throw ValidationException::withMessages(['image' => 'Foto tidak bisa dibaca. Coba foto lain, ya.']);
        }
        $source = $this->applyExifOrientation($source, $file);

        [$w, $h] = [imagesx($source), imagesy($source)];
        $scale = min(1, self::MAX_DIMENSION / max($w, $h));
        [$nw, $nh] = [max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale))];

        // Latar putih agar PNG transparan tidak menjadi hitam saat dijadikan JPEG.
        $canvas = imagecreatetruecolor($nw, $nh);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = (string) ob_get_clean();

        $path = $folder.'/'.Str::uuid().'.jpg';
        Storage::disk($disk)->put($path, $jpeg);

        return ['path' => $path, 'width' => $nw, 'height' => $nh, 'size' => strlen($jpeg)];
    }

    /** Foto dari HP sering disimpan miring dengan penanda EXIF; putar dulu sebelum EXIF dibuang. */
    protected function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }
        $orientation = (int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /**
     * Kenali link YouTube / Spotify.
     *
     * @return array{provider: string, kind: string, id: string, url: string}|null
     */
    public function parseSongLink(string $url): ?array
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }
        $host = Str::lower(preg_replace('/^(www|m)\./', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $youtubeId = match (true) {
            $host === 'youtu.be' => trim($path, '/'),
            in_array($host, ['youtube.com', 'music.youtube.com'], true) && $path === '/watch' => $query['v'] ?? '',
            in_array($host, ['youtube.com', 'music.youtube.com'], true) && preg_match('#^/(shorts|embed|live)/([^/]+)#', $path, $m) === 1 => $m[2],
            default => null,
        };
        if ($youtubeId !== null) {
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId) === 1
                ? ['provider' => 'youtube', 'kind' => 'video', 'id' => $youtubeId, 'url' => 'https://www.youtube.com/watch?v='.$youtubeId]
                : null;
        }

        if ($host === 'open.spotify.com' && preg_match('#^/(?:intl-[a-z-]+/)?(track|album|playlist|episode|show)/([A-Za-z0-9]{22})#', $path, $m) === 1) {
            return ['provider' => 'spotify', 'kind' => $m[1], 'id' => $m[2], 'url' => 'https://open.spotify.com/'.$m[1].'/'.$m[2]];
        }

        return null;
    }

    /**
     * Link lagu dari form (catatan mood, Cerita Teman) menjadi data lagu lengkap; null bila kosong.
     *
     * @return array<string, ?string>|null
     *
     * @throws ValidationException bila link bukan dari YouTube/Spotify
     */
    public function songFromInput(?string $url, string $field = 'song_url'): ?array
    {
        if (blank($url)) {
            return null;
        }
        $song = $this->parseSongLink(preg_match('#^https?://#i', trim($url)) ? $url : 'https://'.trim($url));
        if (! $song) {
            throw ValidationException::withMessages([$field => 'Link lagu harus dari YouTube atau Spotify.']);
        }

        return $song + $this->songDetails($song);
    }

    /**
     * Judul & gambar lagu lewat oEmbed publik (tanpa API key). Gagal = tetap terkirim tanpa judul.
     *
     * @param  array{provider: string, url: string}  $song
     * @return array{title: ?string, author: ?string, thumbnail: ?string}
     */
    public function songDetails(array $song): array
    {
        $endpoint = $song['provider'] === 'youtube' ? 'https://www.youtube.com/oembed' : 'https://open.spotify.com/oembed';
        try {
            $data = Http::timeout(4)->acceptJson()->get($endpoint, ['url' => $song['url'], 'format' => 'json'])->throw()->json();
        } catch (Throwable) {
            return ['title' => null, 'author' => null, 'thumbnail' => null];
        }

        $thumbnail = $data['thumbnail_url'] ?? null;

        return [
            'title' => isset($data['title']) ? Str::limit(strip_tags((string) $data['title']), 150) : null,
            'author' => isset($data['author_name']) ? Str::limit(strip_tags((string) $data['author_name']), 100) : null,
            'thumbnail' => is_string($thumbnail) && str_starts_with($thumbnail, 'https://') ? $thumbnail : null,
        ];
    }

    /** URL embed pemutar (YouTube versi tanpa cookie). */
    public static function embedUrl(array $song): string
    {
        return $song['provider'] === 'youtube'
            ? 'https://www.youtube-nocookie.com/embed/'.$song['id'].'?autoplay=1&rel=0'
            : 'https://open.spotify.com/embed/'.$song['kind'].'/'.$song['id'];
    }
}
