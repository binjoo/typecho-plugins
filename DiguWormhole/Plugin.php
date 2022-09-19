<?php
namespace TypechoPlugin\DiguWormhole;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Cookie;
use Widget\Archive;
use Widget\Feedback;
use Widget\Base\Comments;
use Widget\Comments\Archive as CommentsArchive;

/**
 * 嘀咕虫洞，评论是否来自虫洞的另一端？
 * 
 * @package DiguWormhole
 * @author 冰剑
 * @version 1.0.1
 * @link https://digu.plus
 */
class Plugin implements PluginInterface
{
  /**
   * 激活插件方法,如果激活失败,直接抛出异常
   *
   * @access public
   * @return void
   * @throws Typecho_Plugin_Exception
   */
  public static function activate() {
    Archive::pluginHandle()->handleInit =  __CLASS__ . '::referrer';
    Feedback::pluginHandle()->comment =  __CLASS__ . '::updateAgent';
  }
  
  /**
   * 禁用插件方法,如果禁用失败,直接抛出异常
   *
   * @static
   * @access public
   * @return void
   * @throws Typecho_Plugin_Exception
   */
  public static function deactivate(){
  }
  
  /**
   * 获取插件配置面板
   *
   * @access public
   * @param Typecho_Widget_Helper_Form $form 配置面板
   * @return void
   */
  public static function config(Form $form){
  }

  /**
   * 个人用户的配置面板
   *
   * @access public
   * @param Typecho_Widget_Helper_Form $form
   * @return void
   */
  public static function personalConfig(Form $form){} 

  public static function referrer($handle) {
    $referer = $handle->request->getHeader('referer');
    if($referer && stripos($referer, 'foreverblog')){
      Cookie::set('__typecho_foreverblog', true);
    }
  }

  public static function updateAgent($comment) {
    if(Cookie::get('__typecho_foreverblog')){
      $comment['agent'] = $comment['agent'] . ' foreverblog';
    }
    return $comment;
  }
}