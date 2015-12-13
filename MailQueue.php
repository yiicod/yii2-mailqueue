<?php

namespace yiicod\mailqueue;

use Yii;
use yii\base\Component;
use yii\console\Application;
use yii\helpers\ArrayHelper;

/**
 * Comments extension settings
 * @author Orlov Alexey <aaorlov88@gmail.com>
 */
class MailQueue extends Component
{

    /**
     * @var ARRAY table settings
     */
    public $modelMap = [];

    /**
     * @var string Component name, default PhpMailer
     */
    public $mailer = null;

    /**
     * @var ARRAY components settings
     */
    public $components = [];

    /**
     * @var Array
     */
    public $commandMap = [];

    public function init()
    {
        parent::init();
        
        //Merge main extension config with local extension config
        $config = include(dirname(__FILE__) . '/config/main.php');
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->{$key} = ArrayHelper::merge($value, $this->{$key});
            } elseif (null === $this->{$key}) {
                $this->{$key} = $value;
            }
        }

        if (Yii::$app instanceof Application) {
            //Merge commands map
            Yii::$app->controllerMap = ArrayHelper::merge($this->commandMap, Yii::$app->controllerMap);
            Yii::$app->controllerMap = array_filter(Yii::$app->controllerMap);
        }

        Yii::$app->setComponents($this->components);
        
        //Set components
        if (count($this->components)) {
            $exists = Yii::$app->getComponents(false);
            foreach ($this->components as $component => $params) {
                if (isset($exists[$component]) && is_object($exists[$component])) {
                    unset($this->components[$component]);
                } elseif (isset($exists[$component])) {
                    $this->components[$component] = ArrayHelper::merge($params, $exists[$component]);
                }
            }
            
            Yii::$app->setComponents(
                $this->components, false
            );
        }        
        
        Yii::setAlias('@yiicod', realpath(dirname(__FILE__) . '/..'));
    }

}
