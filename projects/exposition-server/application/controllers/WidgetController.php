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

require_once 'Parser/Factory.php';
require_once 'Compiler/Factory.php';

/**
 * Widget controller.
 */
class WidgetController extends Zend_Controller_Action
{
    /**
     * Widget.
     *
     * @var Widget
     */
    private $_widget = null;

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
        $this->_cache = empty($_GET['nocache']) ? true : false;

        // UWA widget URL
        $this->uwaUrl = $this->getRequest()->getParam('uwaUrl');

        if (!empty($this->uwaUrl)) {
            // Parse the UWA widget from the given URL
            $parser = Parser_Factory::getParser('uwa', $this->uwaUrl, $this->_cache);
            $this->_widget = $parser->buildWidget();
        } else {
            // Create an empty widget
            $this->_widget = new Widget();
            $this->_widget->setBody('<p>This widget cannot be displayed.</p>');
        }

        // Prevent default rendering
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Renders the widget in standalone mode with XML well-formedness.
     */
    public function uwaAction()
    {
        $compiler = Compiler_Factory::getCompiler('uwa', $this->_widget);
        $content = $compiler->render();
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
        $compiler = Compiler_Factory::getCompiler('uwa', $this->_widget);
        $content = $compiler->renderCss();
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
        $compiler = Compiler_Factory::getCompiler('uwa', $this->_widget);
        $options = array(
            'uwaId' => $this->getRequest()->getParam('uwaId'),
            'platform' => $this->getRequest()->getParam('platform')
        );
        $compiler->setOptions($options);
        $content = $compiler->renderJs();
        $this->getResponse()
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget as a Google Gadget specification
     * http://code.google.com/apis/gadgets/docs/dev_guide.html
     */
    public function gspecAction()
    {
        $compiler = Compiler_Factory::getCompiler('google', $this->_widget);
        $options = array( 'type' => isset($_GET['type']) ? $_GET['type'] : 'url' );
        $compiler->setOptions($options);
        $content = $compiler->render();
        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

    /**
     * Renders the widget within an iframe.
     */
    public function frameAction()
    {
        $compiler = Compiler_Factory::getCompiler('frame', $this->_widget);

        if ($this->getRequest()->has('synd') && $this->getRequest()->has('libs')) {
            $compiler->setEnvironment('Frame_Google');
            $options = $this->_getFrameOptionsGoogle();
        } else {
            $options = $this->_getFrameOptions();
        }

        $compiler->setOptions($options);

        Zend_Layout::getMvcInstance()->enableLayout()->setLayout('frame');

        $this->view->bodyClass = 'moduleIframe';
        if (isset($options['chromeColor'])) {
            $this->view->bodyClass .= ' ' .  $options['chromeColor'] . '-module';
        }

        $this->view->headTitle( $this->_widget->getTitle() );

        foreach ($compiler->getStylesheets() as $stylesheet) {
            $this->view->headLink()->appendStylesheet($stylesheet, 'screen');
        }

        if ($this->getRequest()->has('libs')) {
            $libraries = split(',', $this->getRequest()->getParam('libs'));
            foreach ($libraries as $script) {
                if (preg_match('@^[a-z0-9/._-]+$@i', $script) && !preg_match('@([.][.])|([.]/)|(//)@', $script)) {
                    $this->view->headScript()->appendFile("http://www.google.com/ig/f/$script");
                }
            }
        }

        $content = $compiler->getHtmlBody();

        $this->getResponse()
            ->setHeader('Content-Type', 'text/html')
            ->appendBody($content);
    }

    private function _getFrameOptions()
    {
        $options = array( 'data' => array(), 'properties' => array() );

        $ignoredParams = array('id', 'uwaUrl', 'ifproxyUrl', 'header', 'status');
        foreach ($_GET as $name => $value) {
            if (!in_array($name, $ignoredParams) && substr($name, 0, 2) != 'NV') {
                // Fix a problem when displaying the default webnote of netvibes for example
                $value = stripslashes($value);
                $options['data'][$name] = $value;
            }
        }

        foreach (array('NVlang', 'NVlocale', 'NVdir') as $pname) {
            if (isset($_GET[$pname])) {
                $name = substr($pname, 2);
                $options['properties'][$name] = $_GET[$pname];
            }
        }

        $options['properties']['id'] = $this->getRequest()->getParam('id');

        $options['displayHeader'] = $this->getRequest()->getParam('header', '0');
        $options['displayStatus'] = $this->getRequest()->getParam('status', '1');
        $options['ifproxyUrl']    = $this->getRequest()->getParam('ifproxyUrl');
        $options['chromeColor']   = $this->getRequest()->getParam('chromeColor');

        return $options;
    }

    private function _getFrameOptionsGoogle()
    {
        $options = array( 'data' => array(), 'displayStatus' => 1 );
        foreach ($_GET as $name => $value) {
            if (substr($name, 0, 4) == 'upt_') {
                continue;
            }
            if (substr($name, 0, 3) == 'up_') {
                $name = substr($name, 3);
                // Fix a problem when displaying the default webnote of netvibes for example
                $value = stripslashes($value);
                $options['data'][$name] = $value;
            }
        }
        return $options;
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
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Apple Dashboard package.
     */
    public function dashboardAction()
    {
        $compiler = Compiler_Factory::getCompiler('Dashboard', $this->_widget);
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );
        $compiler->setOptions($options);
        $content = $compiler->getFileContent();
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
        $compiler = Compiler_Factory::getCompiler('Screenlets', $this->_widget);
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );
        $compiler->setOptions($options);
        $content = $compiler->getFileContent();
        $this->getResponse()
            ->setHeader('Pragma', 'public')
            ->setHeader('Content-Type', $compiler->getFileMimeType())
            ->setHeader('Content-Disposition', 'attachment; filename="' . $compiler->getFileName() . '"')
            ->appendBody($content);
    }

    /**
     * Renders the widget as an Opera package.
     */
    public function operaAction()
    {
        $compiler = Compiler_Factory::getCompiler('Opera', $this->_widget);
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );
        $compiler->setOptions($options);
        $content = $compiler->getFileContent();
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
        $compiler = Compiler_Factory::getCompiler('Jil', $this->_widget);
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );
        $compiler->setOptions($options);
        $content = $compiler->getFileContent();
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
        $compiler = Compiler_Factory::getCompiler('Vista', $this->_widget);
        $options = array(
            'appendBody' => $this->getRequest()->getUserParam('appendBody')
        );
        $compiler->setOptions($options);
        $content = $compiler->getFileContent();
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
        $compiler = Compiler_Factory::getCompiler('Live', $this->_widget);
        $content = $compiler->render();
        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setHeader('Cache-Control', 'max-age=300')
            ->appendBody($content);
    }

}
