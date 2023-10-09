<?php

namespace TypechoPlugin\HiLinks\Widget\Base;

use Typecho\Common;
use Typecho\Validate;
use Typecho\Db\Exception;
use Typecho\Db\Query;
use Typecho\Plugin;
use Typecho\Router;
use Widget\Base;
use Widget\Base\QueryInterface;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 描述性数据组件
 *
 * @property int $mid
 * @property string $title
 * @property string $url
 * @property string $text
 * @property string $icon
 * @property int $order
 * @property int $status
 * @property-read string $theId
 */
class Links extends Base implements QueryInterface
{
    /**
     * 获取记录总数
     *
     * @param Query $condition 计算条件
     * @return integer
     * @throws Exception
     */
    public function size(Query $condition): int
    {
        return $this->db->fetchObject($condition->select(['COUNT(lid)' => 'num'])->from('table.links'))->num;
    }

    /**
     * 将每行的值压入堆栈
     *
     * @param array $value 每行的值
     * @return array
     */
    public function push(array $value): array
    {
        $value = $this->filter($value);
        return parent::push($value);
    }

    /**
     * 通用过滤器
     *
     * @param array $value 需要过滤的行数据
     * @return array
     */
    public function filter(array $value): array
    {
        $value = Links::pluginHandle()->filter($value, $this);

        if(Validate::email($value['icon'])){
          $rating = $this->options->commentsAvatarRating;
          $value['avatar'] = Common::gravatarUrl($value['icon'], 32, $rating, 'retro');
        }else{
          $value['avatar'] = $value['icon'];
        }

        return $value;
    }

    /**
     * 对数据按照sort字段排序
     *
     * @param array $links
     * @param int $type
     */
    public function sort(array $links, int $mid)
    {
        foreach ($links as $sort => $lid) {
            $this->update(
                ['order' => $sort + 1],
                $this->db->sql()->where('lid = ?', $lid)->where('mid = ?', $mid)
            );
        }
    }

    /**
     * 更新记录
     *
     * @param array $rows 记录更新值
     * @param Query $condition 更新条件
     * @return integer
     * @throws Exception
     */
    public function update(array $rows, Query $condition): int
    {
        return $this->db->query($condition->update('table.links')->rows($rows));
    }

    /**
     * 获取原始查询对象
     *
     * @return Query
     * @throws Exception
     */
    public function select(): Query
    {
        return $this->db->select()->from('table.links');
    }

    /**
     * 删除记录
     *
     * @param Query $condition 删除条件
     * @return integer
     * @throws Exception
     */
    public function delete(Query $condition): int
    {
        return $this->db->query($condition->delete('table.links'));
    }

    /**
     * 插入一条记录
     *
     * @param array $rows 记录插入值
     * @return integer
     * @throws Exception
     */
    public function insert(array $rows): int
    {
        return $this->db->query($this->db->insert('table.links')->rows($rows));
    }

    /**
     * 锚点id
     *
     * @access protected
     * @return string
     */
    protected function ___theId(): string
    {
        return 'link-' . $this->lid;
    }
}
