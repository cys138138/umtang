<?php

namespace umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Elements;

use umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Doctrine\Common\Collections\ArrayCollection;
use umeworld\lib\PHPExcel\PHPExcel\Ticketpark\HtmlPhpExcel\Elements\Base\BaseElement;

/**
 * Table
 *
 * @author Manuel Reinhard <manu@sprain.ch>
 */
class Table extends BaseElement
{
    /**
     * A collection of rows within the table
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $rows;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rows = new ArrayCollection();
        parent::__construct();
    }

    /**
     * Add a row to the table
     *
     * @param \Ticketpark\HtmlPhpExcel\Elements\Row $row
     */
    public function addRow(Row $row)
    {
        $this->rows->add($row);
    }

    /**
     * Get rows of the table
     *
     * @return ArrayCollection
     */
    public function getRows()
    {
        return $this->rows;
    }
}