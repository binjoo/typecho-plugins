<?php

namespace TypechoPlugin\HiLinks\Widget\Groups;

use Typecho\Common;
use Typecho\Db\Exception;
use Typecho\Widget\Helper\Form;
use Widget\Base\Metas;
use Widget\ActionInterface;
use Widget\Notice;
use Utils\Helper;
use TypechoPlugin\HiLinks\Widget\Base\Links;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 标签编辑组件
 *
 * @author qining
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Edit extends Metas implements ActionInterface
{
    /**
     * 入口函数
     */
    public function execute()
    {
        /** 编辑以上权限 */
        $this->user->pass('editor');
    }

    /**
     * 生成表单
     *
     * @param string|null $action 表单动作
     * @return Form
     * @throws Exception
     */
    public function form(?string $action = null): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/Links?group'), Form::POST_METHOD);

        /** 分组名称 */
        $name = new Form\Element\Text(
            'name',
            null,
            null,
            _t('分组名称') . ' *',
            _t('链接分组的名称，如 "博友".')
        );
        $form->addInput($name);

        /** 链接描述 */
        $description = new Form\Element\Textarea(
          'description',
          null,
          null,
          _t('分组描述'),
          _t('链接分组的描述，可用于主题中展示.')
        );
        $form->addInput($description);

        /** 链接动作 */
        $do = new Form\Element\Hidden('do');
        $form->addInput($do);

        /** 链接主键 */
        $mid = new Form\Element\Hidden('mid');
        $form->addInput($mid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (isset($this->request->mid) && 'insert' != $action) {
            /** 更新模式 */
            $group = $this->db->fetchRow($this->select()
                ->where('mid = ?', $this->request->mid)
                ->limit(1));
                
            if (!$group) {
                $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
            }

            $mid->value($group['mid']);
            $name->value($group['name']);
            $description->value($group['description']);

            $do->value('update');
            $submit->value(_t('编辑分组'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加分组'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $name->addRule('required', _t('必须填写链接名称'));
        }

        return $form;
    }

    /**
     * 插入分组
     *
     * @throws Exception
     */
    public function insertGroup()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $group = $this->request->from(
          'name',
          'description'
        );

        $order = $this->getMaxOrder('link');

        $group['type'] = 'link';
        $group['slug'] = $group['name'];
        $gourp['count'] = 0;
        $group['order'] = $order + 1;
        $group['parent'] = 0;
        $group['type'] = 'link';
	
        // /** 插入数据 */
        $group['mid'] = $this->insert($group);
        $this->push($group);

        // /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()->set(
            _t('分组 %s 已经被增加', $this->name),
            'success'
        );

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
    }

    /**
     * 更新分组
     *
     * @throws Exception
     */
    public function updateGroup()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $group = $this->request->from(
          'name',
          'description',
          'mid'
        );

        $group['type'] = 'link';
        /** 更新数据 */
        $this->update($group, $this->db->sql()->where('mid = ?', $this->request->filter('int')->mid));
        $this->push($group);

        /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()->set(
            _t('标签 %s 已经被更新', $this->name),
            'success'
        );

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
    }

    /**
     * 删除分组
     *
     * @throws Exception
     */
    public function deleteGroup()
    {
        $groups = $this->request->filter('int')->getArray('mid');
        $deleteCount = 0;

        if ($groups && is_array($groups)) {
            foreach ($groups as $group) {
                if ($this->delete($this->db->sql()->where('mid = ?', $group))) {
                    $this->db->query(
                      $this->db->delete('table.links')->where('mid = ?', $group)
                    );
                    $deleteCount++;
                }
            }
        }
        /** 提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('分组已经删除') : _t('分组链接被删除'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
    }

    /**
     * 分组排序
     *
     * @throws Exception
     */
    public function sortGroup()
    {
        $mids = $this->request->filter('int')->getArray('mid');
        if ($mids) {
            $this->sort($mids, 'link');
        }

        if (!$this->request->isAjax()) {
            /** 转向原页 */
            $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
        } else {
            $this->response->throwJson(['success' => 1, 'message' => _t('分组排序已经完成')]);
        }
    }

    /**
     * 合并分组
     *
     * @throws Exception
     */
    public function mergeGroup()
    {
        $mids = $this->request->filter('int')->getArray('mid');
        $mergeCount = 0;

        if ($mids && is_array($mids)) {
            foreach ($mids as $mid) {
                if ($this->db->query(
                  $this->db->update('table.links')->where('mid = ?', $mid)->rows([
                    'mid' => $this->request->merge
                  ])
                )) {
                    $mergeCount++;
                }
            }

          array_push($mids, $this->request->merge);
          foreach ($mids as $mid) {
            $row['count'] = $this->db->fetchObject(
              $this->db->select(['COUNT(lid)' => 'num'])->from('table.links')->where('mid = ?', $mid)
            )->num;
            $this->db->query(
              $this->db->update('table.metas')->where('mid = ?', $mid)->rows($row)
            );
          }
        }

        $this->response->redirect(Helper::url('HiLinks/Page/manage-groups.php', $this->options->adminUrl));
    }

    /**
     * 入口函数,绑定事件
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public function action()
    {
        $this->security->protect();
        $this->on($this->request->is('do=insert'))->insertGroup();
        $this->on($this->request->is('do=update'))->updateGroup();
        $this->on($this->request->is('do=delete'))->deleteGroup();
        $this->on($this->request->is('do=sort'))->sortGroup();
        $this->on($this->request->is('do=merge'))->mergeGroup();
        $this->response->redirect($this->options->adminUrl);
    }
}
