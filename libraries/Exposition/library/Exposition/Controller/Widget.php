<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Server.
 *
 * Exposition PHP Server is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exposition PHP Server is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Server. If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'Zend/Controller/Action.php';
require_once 'Zend/Json.php';

require_once 'Exposition/Parser/Factory.php';
require_once 'Exposition/Compiler/Factory.php';

/**
 * Widget controller.
 */
class Exposition_Controller_Widget extends Zend_Controller_Action
{
    /**
     * Widget.
     *
     * @var Exposition_Widget
     */
    private $_widget = null;

    /**
     * uwaUrl.
     *
     * @var string
     */
    private $_uwaUrl = null;

    /**
     * Either to use the parsing cache.
     *
     * @var boolean
     */
    private $_cache = false;

    /**
     * Pre-dispatch routine to parse and build the widget.
     */
    public function preDispatch()
    {
        if (!Exposition_Load::hasConfigLoaded()) {
            throw new Exposition_Exception('Exposition_Load config is not loaded, please use Exposition_Load::setConfig() in your Zend_Application_Bootstrap');
        }

        // Flush cache of parsor
        $this->_cache = $this->getRequest()->getParam('nocache', false);

        try {

            $this->_uwaUrl = $this->_getUwaUrlParam();

            // Parse the UWA widget from the given URL
            $parser = Exposition_Parser_Factory::getParser('uwa', $this->_uwaUrl, $this->_cache);
            $this->_widget = $parser->buildWidget();

        } catch (Exception $e) {

            // Create an empty widget
            $this->_widget = new Exposition_Widget($this->_uwaUrl);
            $this->_widget->setTitle('Widget Error');

            if (0) {
                $this->_widget->setBody('<p>This widget cannot be displayed.</p>');
            } else {
                $this->_widget->setBody(sprintf('<p>This widget cannot be displayed cause: %s.</p>', $e->getMessage()));
            }
        }

        // Prevent default rendering
        $this->_helper->viewRenderer->setNoRender(true);
    }

    private function _getUwaUrlParam()
    {
        // UWA widget URL
        $uwaUrl = $this->getRequest()->getParam('uwaUrl');

        // Get uwaUrl has first GET param key due live.com bug with urldecode
        if (empty($uwaUrl)) {

            $matches = array();
            $pattern = ':\?([^&]*):i';
            if(preg_match ($pattern, $_SERVER['REQUEST_URI'], $matches)) {
                $uwaUrl = urldecode($matches[1]);
            }
        }

        if (empty($uwaUrl) || $uwaUrl == 'uwaUrl=') {
            throw new Exposition_Exception('Unable to get uwaUrl param');
        }

        return $uwaUrl;
    }

    /**
     * Check if a newest version of widget if available
     */
    public function checkforupdateAction()
    {
        $comparedVersion = $this->getRequest()->getParam('v', 0);

        $response = $this->_widget->checkForUpdate($comparedVersion);
        $content = Zend_Json::encode($response);

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->appendBody($content);
    }

    /**
     * Renders the widget in standalone mode with XML well-formedness.
     */
    public function uwaAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('uwa', $this->_widget);
        $content = $compiler->render();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'application/xhtml+xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget in standalone mode with XML well-formedness and Iphone Skin
     */
    public function iphoneAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('iphone', $this->_widget);
        $content = $compiler->render();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'application/xhtml+xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget styles.
     */
    public function cssAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('uwa', $this->_widget);
        $content = $compiler->renderCss();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'text/css; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget javascript.
     */
    public function jsAction()
    {
        $options = array(
            'uwaId'     => $this->getRequest()->getParam('uwaId'),
            'platform'  => $this->getRequest()->getParam('platform'),
            'className' => $this->getRequest()->getParam('className'),
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('uwa', $this->_widget, $options);
        $content = $compiler->renderJs();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget as JSON.
     */
    public function jsonAction()
    {
        $object = array(
            'title'         => $this->_widget->getTitle(),
            'icon'          => $this->_widget->getIcon(),
            'metas'         => $this->_widget->getMetas(),
            'preferences'   => $this->_widget->getPreferencesArray()
        );

        $content = Zend_Json::encode($object);

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Apple Dashboard package.
     */
    public function dashboardAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Dashboard', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'public')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'attachment; filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Screenlets package for Gnome.
     */
    public function screenletsAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Screenlets', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'attachment; filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Opera package.
     */
    public function operaAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Opera', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Chrome package.
     */
    public function chromeAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getParam('appendBody'),
            'disableCrx' => $this->getRequest()->getParam('disableCrx'),
            'privateKey' => $this->getRequest()->getParam('privateKey')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Chrome', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    public function chromePemAction()
    {
        // add previous request checkiing on chromeAction
    }

    /**
     * Renders the widget as an Adobe Air package.
     */
    public function airAction()
    {
        $options = array(
            'privateKey' => $this->getRequest()->getParam('privateKey')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Air', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an JIL package.
     */
    public function jilAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Jil', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Vista package.
     */
    public function vistaAction()
    {
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('Vista', $this->_widget, $options);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as a Microsoft live.com gadget manifest.
     */
    public function liveAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('Live', $this->_widget);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget for blogger as iframe linked to frame compiler
     */
    public function bloggerAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('Blogger', $this->_widget);
        $url = $compiler->getBloggerWidgetUrl();

        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Location', $url);
    }

    /**
     * Renders the widget for firefox
     */
    public function firefoxAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('FireFox', $this->_widget);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget for prism
     */
    public function prismAction()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler('Prism', $this->_widget);
        $content = $compiler->getFileContent();

        // Configure output
        $this->getResponse()
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    //
    // Frame Actions
    //

    /**
     * Renders the widget within an iframe.
     */
    public function frameAction()
    {
        if ($this->getRequest()->has('synd') && $this->getRequest()->has('libs')) {
            $this->_forward('googleframe');
        } else {
            $this->_forward('simpleframe');
        }
    }

    /**
     * Renders the widget within an iframe.
     */
    public function simpleframeAction()
    {
        $options = $this->_getSimpleFrameOptions();
        $compiler = Exposition_Compiler_Factory::getCompiler('frame', $this->_widget, $options);
        $content = $compiler->render();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html')
            ->appendBody($content);
    }

    private function _getSimpleFrameOptions()
    {
        $options = array(
            'displayHeader' => $this->getRequest()->getParam('header', 0),
            'displayStatus' => $this->getRequest()->getParam('status', 1),
            'ifproxyUrl'    => $this->getRequest()->getParam('ifproxyUrl'),
            'chromeColor'   => $this->getRequest()->getParam('chromeColor'),
            'data'          => array(),
            'properties'    => array(
                'id'            => $this->getRequest()->getParam('id', md5($this->_uwaUrl)),
            )
        );

        $ignoredParams = array(
            'uwaId',
            'header',
            'status',
            'uwaUrl',
            'ifproxyUrl',
            'chromeColor',
        );

        foreach ($_GET as $name => $value) {
            if (!in_array($name, $ignoredParams)) {
                $value = stripslashes($value);
                $options['data'][$name] = $value;
            }
        }

        return $options;
    }

    //
    // Google Actions
    //

    /**
     * Renders the widget within an iframe.
     */
    public function googleframeAction()
    {
        $options = $this->_getFrameOptionsGoogle();
        $compiler = Exposition_Compiler_Factory::getCompiler('frame', $this->_widget, $options);
        $compiler->setEnvironment('Frame_Google');
        $content = $compiler->render();

        // Configure output
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html')
            ->appendBody($content);
    }

    /**
     * Renders the widget as a Google Gadget specification
     * http://code.google.com/apis/gadgets/docs/dev_guide.html
     */
    public function gspecAction()
    {
        $options = array(
            'type' => (isset($_GET['type']) ? $_GET['type'] : 'url'),
        );

        $compiler = Exposition_Compiler_Factory::getCompiler('google', $this->_widget, $options);
        $content = $compiler->render();

        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    private function _getFrameOptionsGoogle()
    {
        $options = array(
            'data'          => array(),
            'displayStatus' => $this->getRequest()->getParam('status', '1')
        );

        foreach ($_GET as $name => $value) {

            if (substr($name, 0, 4) == 'upt_') {
                continue;
            }

            // Fix a problem when displaying the default webnote of netvibes for example
            if (substr($name, 0, 3) == 'up_') {
                $name = substr($name, 3);
                $value = stripslashes($value);
                $options['data'][$name] = $value;
            }
        }

        // add google libs require for height resizing
        if ($this->getRequest()->has('libs')) {

            $libraries = split(',', $this->getRequest()->getParam('libs'));
            $externalsScripts = $this->_widget->getExternalScripts();

            foreach ($libraries as $script) {
                if (preg_match('@^[a-z0-9/._-]+$@i', $script) && !preg_match('@([.][.])|([.]/)|(//)@', $script)) {
                    $externalsScripts[] = "http://www.google.com/ig/f/$script";
                }
            }

            $this->_widget->setExternalScripts($externalsScripts);
        }

        return $options;
    }
}

