<?php

/**
 * @copyright Copyright (c) 2013-2015 2amigOS! Consulting Group LLC
 * @link http://2amigos.us
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace dosamigos\ckeditor;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use wfcreations\ckfinder\bundles\CKFinderAsset;

/**
 * CKEditor renders a CKEditor js plugin for classic editing.
 * @see http://docs.ckeditor.com/
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 * @package dosamigos\ckeditor
 */
class CKEditor extends InputWidget {

    use CKEditorTrait;

    public $enabledKCFinder = true;
    public $editorOptions;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        $this->initOptions();
        //$view = $this->getView();
        $id = Json::encode($this->options['id']);
        if ($this->enabledKCFinder) {
            $kcFinderBundle = CKFinderAsset::register($this->getView());
            $kcFinderBaseUrl = $kcFinderBundle->baseUrl;
            // Add KCFinder-specific config for CKEditor
            $this->editorOptions = ArrayHelper::merge(
                            $this->editorOptions, [
                        'filebrowserBrowseUrl' => $kcFinderBaseUrl . '/ckfinder.html',
                        'filebrowserUploadUrl' => $kcFinderBaseUrl . '/core/connector/php/connector.php?command=QuickUpload&type=Files',
                            ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }
        $this->registerPlugin();
    }

    /**
     * Registers CKEditor plugin
     * @codeCoverageIgnore
     */
    protected function registerPlugin() {
        $js = [];

        $view = $this->getView();

        CKEditorWidgetAsset::register($view);

        $id = $this->options['id'];

        $options = $this->editorOptions !== false && !empty($this->editorOptions) ? Json::encode($this->editorOptions) : '{}';

        $js[] = "CKEDITOR.replace('$id', $options);";
        $js[] = "dosamigos.ckEditorWidget.registerOnChangeHandler('$id');";
        if (isset($this->editorOptions['filebrowserUploadUrl'])) {
            $js[] = "dosamigos.ckEditorWidget.registerCsrfImageUploadHandler();";
        }

        $view->registerJs(implode("\n", $js));
    }

}
