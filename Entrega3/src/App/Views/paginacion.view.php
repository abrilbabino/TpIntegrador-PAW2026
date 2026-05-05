<div class="paginacion">
    <?php if ($pagination->hasPrev()): ?>
        <a href="?<?= http_build_query(array_merge($request->getAll(), ['pagina' => $pagination->currentPage - 1])) ?>" class="Boton">Atrás</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination->totalPages; $i++): ?>
        <a href="?<?= http_build_query(array_merge($request->getAll(), ['pagina' => $i])) ?>"
           class="<?= $i === $pagination->currentPage ? 'pagina-activa' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($pagination->hasNext()): ?>
        <a href="?<?= http_build_query(array_merge($request->getAll(), ['pagina' => $pagination->currentPage + 1])) ?>" class="Boton">Siguiente</a>
    <?php endif; ?>
</div>
