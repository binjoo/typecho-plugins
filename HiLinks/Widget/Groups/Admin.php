<?php

namespace TypechoPlugin\HiLinks\Widget\Groups;

use Typecho\Common;
use Typecho\Db;
use Widget\Base\Metas;

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
class Admin extends Metas
{
    /**
     * 入口函数
     *
     * @throws Db\Exception
     */
    public function execute()
    {
      $select = $this->select()->where('type = ?', 'link')->order('order', Db::SORT_ASC)->order('mid', Db::SORT_DESC);
      $this->db->fetchAll($select, [$this, 'push']);
    }
}
