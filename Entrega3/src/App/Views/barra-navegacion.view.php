<header class="Barra-navegacion">
    <label for="menu-hamburguesa" class="label-hamburguesa">
      <img
        src="/assets/img/menu-hamburguesa.png"
        alt="Abrir menú"
        class="icono-header"
      />
    </label>

    <figure class="header-logo">
      <img src="/assets/img/logo.png" alt="PAWPrints" />
    </figure>

    <form action="/buscar" method="GET" class="header-busqueda">
      <label for="busqueda" class="busqueda-label"
        >Buscar en el sitio..</label
      >
      <input
        type="search"
        id="busqueda"
        name="busqueda"
        placeholder="Buscar en el sitio..."
        class="busqueda-input"
      />
      <button type="submit" class="busqueda-btn">Buscar</button>
    </form>
    
    <nav class="menu-principal">
    <input type="checkbox" id="menu-hamburguesa" class="menu-hamburguesa-check" />

    <ul class="nav-lista">
        <?php foreach ($menu as $item) : ?>
            <li class="<?= $item['li_class'] ?? '' ?>">
                
                <?php if ($item['type'] === 'link') : ?>
                    <a href="<?= $item['href'] ?>" class="nav-link <?= $item['class'] ?? '' ?>">
                        <span class="material-symbols-outlined simbolos"><?= $item['icon'] ?></span>
                        <?= $item['name'] ?>
                    </a>

                <?php else : ?>
                    <label for="<?= $item['for'] ?>" class="nav-link">
                        <span class="material-symbols-outlined simbolos"><?= $item['icon'] ?></span>
                        <?= $item['name'] ?>
                    </label>
                <?php endif; ?>

            </li>
        <?php endforeach; ?>
    </ul>
  </nav>
</header>