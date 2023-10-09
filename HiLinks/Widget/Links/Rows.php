<?php

namespace TypechoPlugin\HiLinks\Widget\Links;

use Typecho\Common;
use Typecho\Db;
use TypechoPlugin\HiLinks\Widget\Base\Links;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

class Rows extends Links
{
    /**
     * 入口函数
     *
     * @throws Db\Exception
     */
    public function execute()
    {
      $select = $this->select()->where('mid = ?', $this->parameter->mid ?? 0);
      $select->order('order', Db::SORT_ASC);
      $this->db->fetchAll($select, [$this, 'push']);
    }

    public function findByMid($mid)
    {
      $select = $this->select()->where('mid = ?', $mid ?? 0);
      $select->order('order', Db::SORT_ASC);
      $this->db->fetchAll($select, [$this, 'push']);
    }
}
