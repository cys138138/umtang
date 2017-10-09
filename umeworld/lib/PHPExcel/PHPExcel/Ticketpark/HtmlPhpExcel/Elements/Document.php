<?php

namespace umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Elements;

use umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Doctrine\Common\Collections\ArrayCollection;

/**
 * Document
 *
 * @author Manuel Reinhard <manu@sprain.ch>
 */
class Document
{
    /**
     * A collection of tables within the document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $tables;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tables = new ArrayCollection();
    }

    /**
     * Add a table to the document
     *
     * @param \Ticketpark\HtmlPhpExcel\Elements\Table $table
     */
    public function addTable(Table $table)
    {
        $this->tables->add($table);
    }

    /**
     * Get tables of the document
     *
     * @return ArrayCollection
     */
    public function getTables()
    {
        return $this->tables;
    }
}