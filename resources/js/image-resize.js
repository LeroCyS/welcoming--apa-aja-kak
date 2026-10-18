/**
 * Foto dari HP sering 3-8 MB, sementara server bisa menolak unggahan di atas 2 MB (upload_max_filesize).
 * Foto diperkecil & dikompres di browser sebelum dikirim. GIF dibiarkan apa adanya.
 */
export async function shrinkImage(file, { maxDimension = 1600, quality = 0.85, maxBytes = 1.5 * 1024 * 1024 } = {}) {
    if (!file || !file.type?.startsWith('image/') || file.type === 'image/gif') return file;
    let bitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch (e) {
        return file;
    }
    const scale = Math.min(1, maxDimension / Math.max(bitmap.width, bitmap.height));
    if (scale === 1 && file.size <= maxBytes) { bitmap.close?.(); return file; }
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close?.();
    let q = quality;
    let blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', q));
    while (blob && blob.size > maxBytes && q > 0.5) {
        q -= 0.1;
        blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', q));
    }
    if (!blob) return file;
    return new File([blob], (file.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
}

/** Ganti isi <input type="file"> dengan versi yang sudah diperkecil (untuk form biasa). */
export async function shrinkInput(input, options) {
    const file = input.files?.[0];
    if (!file) return null;
    const small = await shrinkImage(file, options);
    if (small !== file && typeof DataTransfer !== 'undefined') {
        const dt = new DataTransfer();
        dt.items.add(small);
        input.files = dt.files;
    }
    return small;
}
