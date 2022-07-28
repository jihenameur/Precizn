<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Request;

class TypeAddress
{
    public $id;
    public $titre;

    public function __construct(int $id,string $titre)
    {
        $this->id=$id;
        $this->titre=$titre;

    }

}
