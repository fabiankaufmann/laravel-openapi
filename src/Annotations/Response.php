<?php

namespace Vyuldashev\LaravelOpenApi\Annotations;

use Doctrine\Common\Annotations\Annotation\Required;
use InvalidArgumentException;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
class Response
{
    /**
     * @Required()
     */
    public $factory;

    public function __construct($values)
    {
        $this->factory = $values['factory'];

        if (!is_a($this->factory, ResponseFactory::class, true)) {
            throw new InvalidArgumentException('Factory class must be instance of ResponseFactory');
        }
    }
}
