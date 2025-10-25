import './bootstrap';

// Zorg dat alle categorie-afbeeldingen in de build/manifest komen:
import.meta.glob('../images/catogorieen-fotos/*.{webp,jpg,jpeg,png,svg}', { eager: true, as: 'url' });

// (Eventueel breder)
// import.meta.glob('../images/**/*.{webp,jpg,jpeg,png,svg}', { eager: true, as: 'url' });

