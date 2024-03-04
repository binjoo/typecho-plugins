<?php

namespace TypechoPlugin\Gemini;

use Typecho\Db;
use Typecho\Http\Client;
use Typecho\Http\Client\Exception;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Select;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Widget\Helper\Form\Element\Radio;
use Widget\Options;
use Utils\Helper;
use Utils\Markdown;

/**
 * 通过 Gemini 针对日志内容做出评论
 * 
 * @package Gemini
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
      if (Options::alloc()->plugin('Gemini')->clear) {
        $db = Db::get();
        if("Pdo_Mysql" === $db->getAdapterName() || "Mysql" === $db->getAdapterName()){
           $db->query($db->delete('table.fields')->where('name = ?', 'gemini_comment'));
        }
      }
    }
   
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Form $form){
      $apiKey = new Text('apiKey', NULL, NULL, 'API KEY', '可以前往 <a target="_blank" href="https://makersuite.google.com/app/apikey">Google AI Studio</a> 或 <a target="_blank" href="https://ai.google.dev">Gemini</a> 进行申请。');
      $apiKey->addRule('required', _t('请填写 Gemini API Key'));
      $form->addInput($apiKey);

      $model = new Select('model', [
        'gemini-pro'  =>  _t('Gemini Pro')
      ], 'gemini-pro', _t('模型'), _t('模型的特性可查看 <a target="_blank" href="https://ai.google.dev/models/gemini">Gemini 模型</a>。'));
      $form->addInput($model);

      $timeout = new Text('timeout', NULL, 6, '请求超时时间', '请求 Gemini 接口超时时间，单位：秒');
      $timeout->input->setAttribute('class', 'mini');
      $form->addInput($timeout);

      $text = new Textarea('text', NULL, _t('我写了一篇日志，标题是`{title}`，内容是`{text}`，作为博客的日常博友，需要你根据日志内容写一段不超过128个汉字的评论，不用太正式，随意一点。'), _t('对话内容'), _t('根据自己的需要进行调整，不要有废话。<br />支持的占位值：{title}：标题、{text}：内容原文、{html}：内容HTML'));
      $apiKey->addRule('required', _t('请填写对话内容'));
      $form->addInput($text);

      $customUrl = new Text('customUrl', NULL, 'https://generativelanguage.googleapis.com/v1/models/{model}:generateContent', '自定义接口地址', '服务器在<a target="_blank" href="https://ai.google.dev/available_regions#available_regions">可用区域</a>的使用默认的即可，中国仅台湾省可用。');
      $form->addInput($customUrl);

      $clear = new Radio('clear',
        [
          '1' => _t('是'),
          '0' => _t('否')
        ], 0,
        _t('删除数据'),
        _t('禁用本插件时，是否删除插件产生的所有数据。<br /><strong class="warning">操作不可逆，请谨慎选择！</strong><br /><strong class="warning">操作不可逆，请谨慎选择！</strong><br /><strong class="warning">操作不可逆，请谨慎选择！</strong>')
      );
      $form->addInput($clear);

      return $form;
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Form $form){}

    public static function comment($post){
      if($post->type != 'post'){
        return false;
      }
      if(isset($post->fields->gemini_comment) || $post->fields->gemini_comment == null){
        $option = Helper::options()->plugin('Gemini');

		    $query = [
          'key' => $option->apiKey
        ];

        $data = array(
          'contents' => array(
            array(
              'parts' => array(
                  array(
                  'text' => str_replace(['{title}', '{text}', '{html}'], [$post->title, $post->text, Markdown::convert($post->text)], $option->text)
                )
              )
            )
          ),
          'safetySettings' => array(
            array(
              'category' => 'HARM_CATEGORY_HARASSMENT',
              'threshold' => 'BLOCK_NONE'
            ),
            array(
              'category' => 'HARM_CATEGORY_HATE_SPEECH',
              'threshold' => 'BLOCK_NONE'
            ),
            array(
              'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
              'threshold' => 'BLOCK_NONE'
            ),
            array(
              'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
              'threshold' => 'BLOCK_NONE'
            )
          )
        );

        $sslOption = [
          'CURLOPT_SSL_VERIFYPEER' => false,
          'CURLOPT_SSL_VERIFYHOST' => false,
          'CURLOPT_SSLVERSION' => 0
        ];

        $client = Client::get();
        $client->setMethod('POST');
        $client->setHeader('Content-Type', 'application/json');
        $client->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $client->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $client->setOption(CURLOPT_SSLVERSION, 0);
        $client->setTimeout($option->timeout);
        $client->setQuery($query);
        $client->setData(json_encode($data, JSON_UNESCAPED_UNICODE));

        $apiUrl = str_replace('{model}', $option->model, $option->customUrl);
        try {
          $client->send($apiUrl);
          if($client->getResponseStatus() == 200){
            $text = json_decode($client->getResponseBody())->candidates[0]->content->parts[0]->text;
            if($text){
              $post->setField('gemini_comment', 'str', $text, $post->cid);
              $comment = $text;
            }
          }
        } catch (Exception $e) {
        }
      }else{
        $comment = $post->fields->gemini_comment;
      }

      if(isset($comment)){
        return Markdown::convert($comment);
      }else{
        return false;
      }
    }
}