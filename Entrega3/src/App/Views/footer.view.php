<footer class="footer-principal">
    <address class="footer-direccion">
        <p class="footer-email">
            Email: <a href="mailto:info@pawprints.com">info@pawprints.com</a>
        </p>
        <p class="footer-redes">Seguinos en nuestras redes:</p>
        <ul class="footer-redes-lista">
            <?php foreach ($redes as $red) : ?>
                <li>
                    <a href="<?= $red['url'] ?>" class="icono-red">
                        <span class="texto-red"><?= $red['name'] ?></span>
                        <img src="/assets/img/<?= $red['img'] ?>" alt="<?= $red['name'] ?>" class="img-red" />
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </address>
    <p class="footer-copyright"><small>&copy; <?= date('Y') ?> PawMap</small></p>
</footer>