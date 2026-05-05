<?php

namespace Paw\Core;

class Pagination
{
    public int $currentPage;
    public int $perPage;
    public int $total;
    public int $offset;
    public int $totalPages; 

    public function __construct(int $page, int $perPage, int $total)
    {
        $this->perPage    = max(1, $perPage);
        $this->total      = max(0, $total);
        $this->totalPages = (int) ceil($this->total / $this->perPage);
        $this->currentPage = max(1, min($page, $this->totalPages ?: 1));
        $this->offset     = ($this->currentPage - 1) * $this->perPage;
    }
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
}