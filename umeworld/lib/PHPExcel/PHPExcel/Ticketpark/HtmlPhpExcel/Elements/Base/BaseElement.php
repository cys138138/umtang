<?php

namespace umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Elements\Base;

use umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Doctrine\Common\Collections\ArrayCollection;

/**
 * Base Element
 *
 * @author Manuel Reinhard <manu@sprain.ch>
 */
class BaseElement
{
    /**
     * A collection of attributes of this cell
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * Add an attribute
     *
     * @param string $name
     * @param string $value
     */
    public function addAttribute($key, $value)
    {
        $this->attributes->set($key, $value);
    }

    /**
     * Get single attribute
     *
     * @return string
     */
    public function getAttribute($key)
    {
        return $this->attributes->get($key);
    }
}