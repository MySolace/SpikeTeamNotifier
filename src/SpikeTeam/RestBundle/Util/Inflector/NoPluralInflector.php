<?php

namespace SpikeTeam\RestBundle\Util\Inflector;

use FOS\RestBundle\Util\Inflector\InflectorInterface;

/**
 * Inflector interface
 * Adding this in to remove automatic pluralization of routes, a la the documentation at https://github.com/FriendsOfSymfony/FOSRestBundle/pull/870/files
 */
class NoPluralInflector implements InflectorInterface
{
    /**
     * DOES NOT Pluralize noun.
     *
     * @param string $word
     *
     * @return string
     */
    public function pluralize($word)
    {
        return $word;
    }
}
