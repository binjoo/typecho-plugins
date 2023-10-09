<?php

namespace TypechoPlugin\HiLinks\Widget\Links;

use Typecho\Common;
use Typecho\Db;
use Typecho\Widget\Helper\PageNavigator\Box;
use TypechoPlugin\HiLinks\Widget\Base\Links;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 标签云组件
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Admin extends Links
{
    private $countSql;
    private $total;
    private $currentPage;

    /**
     * 入口函数
     *
     * @throws Db\Exception
     */
    public function execute()
    {
      $this->parameter->setDefault('pageSize=10');
      $this->currentPage = $this->request->get('page', 1);

      $select = $this->select();
      if ($this->request->mid) {
        $select->where('mid = ?', $this->request->mid);
      }

      $this->countSql = clone $select;

      $select->order('order', Db::SORT_ASC);

      $this->db->fetchAll($select, [$this, 'push']);
    }
}
