<?php
namespace TypechoPlugin\HiLinks;

use Typecho\Widget;
use Widget\ActionInterface;
use TypechoPlugin\HiLinks\Widget\Links\Edit;

class Action extends Widget implements ActionInterface
{

    public function execute() {}

    public function action(){
        if($this->request->is('link')){  //插件设置业务
            Widget::widget("\TypechoPlugin\HiLinks\Widget\Links\Edit")->action();
        }else if($this->request->is('group')){  //自定义回复业务
            Widget::widget("\TypechoPlugin\HiLinks\Widget\Groups\Edit")->action();
        }
    }
}
?>