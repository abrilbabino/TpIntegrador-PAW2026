<?php 
$baseUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'];
echo '<?xml version="1.0" encoding="UTF-8"?>'; 
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Páginas estáticas -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/adoptar</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/como-adoptar</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/contacto</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/test-de-compatibilidad</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/refugios</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Fichas de Mascotas -->
    <?php if (!empty($mascotas)): ?>
        <?php foreach ($mascotas as $mascota): ?>
        <url>
            <loc><?= $baseUrl ?>/mascota?id=<?= htmlspecialchars($mascota->fields['id'] ?? '') ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Perfiles de Refugios -->
    <?php if (!empty($refugios)): ?>
        <?php foreach ($refugios as $refugio): ?>
        <url>
            <loc><?= $baseUrl ?>/refugio/perfil?id=<?= htmlspecialchars($refugio->fields['usuario_id'] ?? $refugio->fields['id'] ?? '') ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        <?php endforeach; ?>
    <?php endif; ?>
</urlset>
