<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Páginas estáticas -->
    <url>
        <loc>https://pawmap.com.ar/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://pawmap.com.ar/adoptar</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>https://pawmap.com.ar/como-adoptar</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://pawmap.com.ar/contacto</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>https://pawmap.com.ar/test-de-compatibilidad</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>https://pawmap.com.ar/refugios</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Fichas de Mascotas -->
    <?php if (!empty($mascotas)): ?>
        <?php foreach ($mascotas as $mascota): ?>
        <url>
            <loc>https://pawmap.com.ar/mascota?id=<?= htmlspecialchars($mascota->fields['id'] ?? '') ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Perfiles de Refugios -->
    <?php if (!empty($refugios)): ?>
        <?php foreach ($refugios as $refugio): ?>
        <url>
            <loc>https://pawmap.com.ar/refugio/perfil?id=<?= htmlspecialchars($refugio->fields['usuario_id'] ?? $refugio->fields['id'] ?? '') ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        <?php endforeach; ?>
    <?php endif; ?>
</urlset>
