<header class="Barra-navegacion">
    <input type="checkbox" id="menu-hamburguesa" class="menu-hamburguesa-check" />

    <label for="menu-hamburguesa" class="label-hamburguesa">
        <span class="material-symbols-outlined">menu</span>
    </label>

    <figure class="header-logo">
        <img src="/assets/img/logo.png" alt="PawMap" />
    </figure>

    <label for="mostrar-login" class="icono-usuario">
        <span class="material-symbols-outlined">person</span>
    </label>

    <form action="/buscar" method="GET" class="header-busqueda">
        <input type="search" id="busqueda" name="busqueda" placeholder="Buscar..." class="busqueda-input" />
    </form>

    <nav class="menu-principal">
        <ul class="nav-lista">
            <?php foreach ($menu as $item) : ?>
                <li class="<?= $item['li_class'] ?? '' ?>">
                    <?php if (($item['type'] ?? '') === 'link') : ?>
                        <a href="<?= $item['href'] ?>" class="nav-link <?= $item['class'] ?? '' ?>">
                            <?php if (isset($item['icon'])): ?><span class="material-symbols-outlined simbolos"><?= $item['icon'] ?></span><?php endif; ?>
                            <?= $item['name'] ?>
                        </a>
                    <?php elseif (($item['type'] ?? '') === 'label') : ?>
                        <label for="<?= $item['for'] ?? '' ?>" class="nav-link">
                            <?php if (isset($item['icon'])): ?><span class="material-symbols-outlined simbolos"><?= $item['icon'] ?></span><?php endif; ?>
                            <?= $item['name'] ?>
                        </label>
                    <?php else : ?>
                        <span class="nav-link"><?= $item['name'] ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>
