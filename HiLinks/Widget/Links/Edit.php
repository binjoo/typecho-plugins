<?php

namespace TypechoPlugin\HiLinks\Widget\Links;

use Typecho\Common;
use Typecho\Db;
use Typecho\Db\Exception;
use Typecho\Widget\Helper\Form;
use Widget\ActionInterface;
use Widget\Notice;
use Widget\Base\Metas;
use Utils\Helper;
use TypechoPlugin\HiLinks\Widget\Base\Links;
use TypechoPlugin\HiLinks\Widget\Groups\Admin;

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
class Edit extends Links implements ActionInterface
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
        $form = new Form($this->security->getIndex('/action/Links?link'), Form::POST_METHOD);

        /** 链接名称 */
        $title = new Form\Element\Text(
            'title',
            null,
            null,
            _t('链接名称') . ' *'
        );
        $form->addInput($title);

        /** 链接地址 */
        $url = new Form\Element\Text(
            'url',
            null,
            null,
            _t('链接地址') . ' *'
        );
        $form->addInput($url);

        /** 链接状态 */
        $status = new Form\Element\Radio(
          'status',
          [
            '1' => _t('正常'),
            '2' => _t('隐藏'),
            '3' => _t('失联')
          ],
          1,
          _t('链接状态') . ' *',
          _t('对于失联状态的，建议使用 <a target="_blank" href="https://web.archive.org/">WayBack Machine</a> 找到它们当年的模样。')
        );
        $form->addInput($status);

        /** 链接分组 */
        $groups = Admin::alloc();
        if($groups->have()){
          while ($groups->next()) {
              $options[$groups->mid] = $groups->name;
          }
        }else{
          $options[-1] = '默认分组（创建）';
        }
        
        $mid = new Form\Element\Select(
          'mid',
          $options,
          null,
          _t('链接分组') . ' *',
          _t('如无分组则会自动创建默认分组. ')
        );
        $form->addInput($mid);

        /** 链接图标 */
        $icon = new Form\Element\Text(
            'icon',
            null,
            null,
            _t('链接图标'),
            _t('可填写email或者图片链接，如填写email则会展示gravatar。')
        );
        $form->addInput($icon);

        /** 链接描述 */
        $description = new Form\Element\Textarea(
          'description',
          null,
          null,
          _t('链接描述')
        );
        $form->addInput($description);

        /** 链接动作 */
        $do = new Form\Element\Hidden('do');
        $form->addInput($do);

        /** 链接主键 */
        $lid = new Form\Element\Hidden('lid');
        $form->addInput($lid);

        $page = new Form\Element\Hidden('page');
        $form->addInput($page);

        /** 提交按钮 */
        $submit = new Form\Element\Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (isset($this->request->lid) && 'insert' != $action) {
            /** 更新模式 */
            $link = $this->db->fetchRow($this->select()
                ->where('lid = ?', $this->request->lid)
                ->limit(1));

            if (!$link) {
                $this->response->redirect(Helper::url('HiLinks/Page/manage-links.php', $this->options->adminUrl));
            }

            $lid->value($link['lid']);
            $mid->value($link['mid']);
            $title->value($link['title']);
            $url->value($link['url']);
            $status->value($link['status']);
            $description->value($link['description']);
            $icon->value($link['icon']);
            $page->value($this->request->get('page', 1));

            $do->value('update');
            $submit->value(_t('编辑链接'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加链接'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $title->addRule('required', _t('必须填写链接名称'));
            $url->addRule('required', _t('必须填写链接地址'))->addRule('url', _t('请填写一个合法的URL地址'));
            $status->addRule('required', _t('必须选择链接状态'));
            $mid->addRule('required', _t('必须选择链接分组'));
        }

        return $form;
    }

    /**
     * 插入链接
     *
     * @throws Exception
     */
    public function insertLink()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $link = $this->request->from(
          'title',
          'url',
          'status',
          'mid',
          'description',
          'icon'
        );

        if($link['mid'] <= 0){
          $link['mid'] = $this->createGroup();
        }

        // /** 插入数据 */
        $link['lid'] = $this->insert($link);
        $this->push($link);

        // 更新数量
        $this->updateCount();

        // /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()->set(
            _t('链接 %s 已经被增加', $this->title),
            'success'
        );

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-links.php' . ($link['mid'] ? '&mid=' . $link['mid'] : ''), $this->options->adminUrl));
    }

    /**
     * 更新链接
     *
     * @throws Exception
     */
    public function updateLink()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $link = $this->request->from(
          'title',
          'url',
          'status',
          'mid',
          'description',
          'icon',
          'lid'
        );

        /** 更新数据 */
        $this->update($link, $this->db->sql()->where('lid = ?', $this->request->filter('int')->lid));
        $this->push($link);

        // 更新数量
        $this->updateCount();

        /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()->set(
            _t('链接 %s 已经被更新', $link['title']),
            'success'
        );

        $url = 'HiLinks/Page/manage-links.php';
        $url .= $link['mid'] ? '&mid=' . $link['mid'] : '';

        /** 转向原页 */
        $this->response->redirect(Helper::url($url, $this->options->adminUrl));
    }

    /**
     * 删除链接
     *
     * @throws Exception
     */
    public function deleteLink()
    {
        $links = $this->request->filter('int')->getArray('lid');
        $deleteCount = 0;

        if ($links && is_array($links)) {
            foreach ($links as $link) {
                if ($this->delete($this->db->sql()->where('lid = ?', $link))) {
                    $deleteCount++;
                }
            }
        }

        // 更新数量
        $this->updateCount();

        /** 提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('链接已经删除') : _t('没有链接被删除'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-links.php' . ($this->request->mid ? '&mid=' . $this->request->mid : ''), $this->options->adminUrl));
    }

    /**
     * 链接排序
     *
     * @throws Exception
     */
    public function sortLink()
    {
        $lids = $this->request->filter('int')->getArray('lid');
        if ($lids) {
            $this->sort($lids, $this->request->mid ?? 0);
        }

        if (!$this->request->isAjax()) {
            /** 转向原页 */
            $this->response->redirect(Helper::url('HiLinks/Page/manage-links.php', $this->options->adminUrl));
        } else {
            $this->response->throwJson(['success' => 1, 'message' => _t('链接排序已经完成')]);
        }
    }

    /**
     * 移动链接
     *
     * @throws Exception
     */
    public function mergeLink()
    {
        $lids = $this->request->filter('int')->getArray('lid');
        $mergeCount = 0;

        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($this->db->query(
                  $this->db->update('table.links')->where('lid = ?', $lid)->rows([
                    'mid' => $this->request->merge
                  ])
                )) {
                    $mergeCount++;
                }
            }

            $this->updateCount();
        }

        /** 转向原页 */
        $this->response->redirect(Helper::url('HiLinks/Page/manage-links.php', $this->options->adminUrl));
    }

    private function updateCount(){
      $groups = Admin::alloc();
      while ($groups->next()) {
        $row['count'] = $this->db->fetchObject(
          $this->db->select(['COUNT(lid)' => 'num'])->from('table.links')->where('mid = ?', $groups->mid)
        )->num;

        $this->db->query(
          $this->db->update('table.metas')->where('mid = ?', $groups->mid)->rows($row)
        );
      }
    }

    private function createGroup(){
      return $this->db->query(
        $this->db->insert('table.metas')->rows([
          'name' => '默认分组',
          'slug' => '默认分组',
          'type' => 'link',
          'description' => '默认分组',
          'count' => 1,
          'order' => 1,
          'parent' => 0
        ])
      );
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
        $this->on($this->request->is('do=insert'))->insertLink();
        $this->on($this->request->is('do=update'))->updateLink();
        $this->on($this->request->is('do=delete'))->deleteLink();
        $this->on($this->request->is('do=sort'))->sortLink();
        $this->on($this->request->is('do=merge'))->mergeLink();
        $this->response->redirect($this->options->adminUrl);
    }
}
