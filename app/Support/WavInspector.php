<?php

namespace App\Support;

class WavInspector
{
    /** Durasi (detik) dari header WAV PCM; null jika bukan WAV valid. */
    public static function duration(string $path): ?float
    {
        $fh = @fopen($path, 'rb');
        if (! $fh) {
            return null;
        }
        $header = fread($fh, 12);
        if (strlen($header) < 12 || substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WAVE') {
            fclose($fh);

            return null;
        }
        $byteRate = null;
        $dataSize = null;
        while (! feof($fh)) {
            $chunk = fread($fh, 8);
            if (strlen($chunk) < 8) {
                break;
            }
            ['id' => $id, 'size' => $size] = ['id' => substr($chunk, 0, 4), 'size' => unpack('V', substr($chunk, 4, 4))[1]];
            if ($id === 'fmt ') {
                $fmt = fread($fh, $size);
                $byteRate = unpack('V', substr($fmt, 8, 4))[1] ?? null;
            } elseif ($id === 'data') {
                $dataSize = $size;
                break;
            } else {
                fseek($fh, $size, SEEK_CUR);
            }
        }
        fclose($fh);

        return ($byteRate && $dataSize !== null) ? $dataSize / $byteRate : null;
    }
}
