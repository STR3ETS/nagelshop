@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  // Helper: maak altijd een correcte publieke URL van een foto-pad of URL
  $productFotoUrl = function ($foto) {
    if (empty($foto)) return null;

    // Al een volledige URL
    if (Str::startsWith($foto, ['http://', 'https://'])) return $foto;

    // Als er al een public storage pad in zit
    if (Str::startsWith($foto, ['/storage/'])) return $foto;
    if (Str::startsWith($foto, 'storage/')) return '/' . $foto;

    // Normaliseer: 'producten/xxx.jpg' of 'xxx.jpg'
    $path = Str::startsWith($foto, 'producten/') ? $foto : 'producten/' . ltrim($foto, '/');

    // Publieke URL via disk('public') => /storage/...
    return Storage::disk('public')->url($path);
  };
@endphp
