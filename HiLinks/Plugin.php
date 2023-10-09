<?php

namespace TypechoPlugin\HiLinks;

use Typecho\Db;
use Typecho\Widget;
use Typecho\Plugin\PluginInterface;
use Typecho\Plugin\Exception;
use Typecho\Widget\Helper\Form;
use Widget\Options;
use Utils\Helper;

/**
 * 一款服务于博友的友情链接插件
 * 
 * @package HiLinks
 * @author 冰剑
 * @version 1.0.0
 * @link https://digu.plus
 */
class Plugin implements PluginInterface
{
    public static function activate()
    {
        $db = Db::get();
        if("Pdo_Mysql" === $db->getAdapterName() || "Mysql" === $db->getAdapterName()){
            /**
             * 创建链接表
             */
            $db->query("CREATE TABLE IF NOT EXISTS " . $db->getPrefix() . 'links' . " (
                      `lid` int(10) NOT NULL AUTO_INCREMENT,
                      `mid` int(10) default 0,
                      `title` varchar(128) default NULL,
                      `url` varchar(256) default NULL,
                      `icon` varchar(256) default NULL,
                      `description` varchar(256) default NULL,
                      `status` char(1) default 0,
                      `order` int(10) default 0,
                      PRIMARY KEY (`lid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1");

            // $num = $db->fetchObject(
            //   $db->select(['COUNT(mid)' => 'num'])->from('table.metas')->where('type = ?', 'link')
            // )->num;
            // if($num <= 0){
            //   $db->query(
            //     $db->insert('table.metas')->rows([
            //       'name' => '默认分组',
            //       'slug' => '默认分组',
            //       'type' => 'link',
            //       'description' => '默认分组',
            //       'count' => 0,
            //       'order' => 1,
            //       'parent' => 0
            //     ])
            //   );
            // }
        }else{
            throw new Exception(_t('对不起, 本插件仅支持MySQL数据库。'));
        }

        Helper::addAction('Links', 'HiLinks_Action');
        Helper::addPanel(3, 'HiLinks/Page/manage-links.php', '链接', '管理链接', 'administrator');
        Helper::addPanel(3, 'HiLinks/Page/manage-groups.php', '管理 链接分组', '管理分组', 'administrator', true);
        //return('友情链接已经成功激活!');
    }

    public static function deactivate() {
      if (Options::alloc()->plugin('HiLinks')->clear) {
            $db = Db::get();
            if("Pdo_Mysql" === $db->getAdapterName() || "Mysql" === $db->getAdapterName()){
               $db->query("drop table ".$db->getPrefix()."links");
               $db->query($db->delete('table.metas')->where('type = ?', 'link'));
            }
        }

        Helper::removePanel(3, 'HiLinks/Page/manage-links.php');
        Helper::removePanel(3, 'HiLinks/Page/manage-groups.php');
        Helper::removeAction('Links');
    }

    public static function config(Form $form) {
      $clear = new Form\Element\Radio(
        'clear',
        [
          '1' => _t('是'),
          '0' => _t('否')
        ],
        0,
        _t('删除数据'),
        _t('禁用本插件时，是否删除插件产生的所有数据。<strong class="warning">请慎选，一切后果自负。</strong>')
      );
      $form->addInput($clear);
    }
    
    public static function personalConfig(Form $form){}
}