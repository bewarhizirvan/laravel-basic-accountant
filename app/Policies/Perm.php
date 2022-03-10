<?php
namespace App\Policies;

class Perm
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
