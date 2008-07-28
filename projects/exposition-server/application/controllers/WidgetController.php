<?php
/**
 * Copyright (c) 2008 Netvibes (http://www.netvibes.org/).
 *
 * This file is part of Netvibes Widget Platform.
 *
 * Netvibes Widget Platform is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Netvibes Widget Platform is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
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
     * UWA compiler.
     *
     * @var Compiler
     */
    private $_compiler = null;

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

        // Compiler
        if ($this->getRequest()->getParam('action') == 'frame') {
            $this->_compiler = Compiler_Factory::getCompiler('frame', $this->_widget);
        } else if ($this->getRequest()->getParam('action') == 'gspec') {
            $this->_compiler = Compiler_Factory::getCompiler('google', $this->_widget);
        } else {
            $this->_compiler = Compiler_Factory::getCompiler('uwa', $this->_widget);
        }
    }

    /**
     * Renders the widget in standalone mode with XML well-formedness.
     */
    public function uwaAction()
    {
        header('Content-type:application/xhtml+xml; charset=utf-8');
        header('Cache-Control: max-age=300');
        echo $this->_compiler->render();
    }

    /**
     * Renders the widget styles.
     */
    public function cssAction()
    {
        header('Content-type: text/css; charset=utf-8');
        header('Cache-Control: max-age=300');
        echo $this->_compiler->renderCss();
    }

    /**
     * Renders the widget javascript.
     */
    public function jsAction()
    {
        header('Content-type: text/javascript; charset=utf-8');
        header('Cache-Control: max-age=300');
        echo $this->_compiler->renderJs();
    }
    
    /**
     * Renders the widget as a Google Gadget specification
     * http://code.google.com/apis/gadgets/docs/dev_guide.html
     */
    public function gspecAction()
    {
        header('Content-type: text/xml; charset=utf-8');
        header('Cache-Control: max-age=300');
        $options = array( 'type' => isset($_GET['type']) ? $_GET['type'] : 'url' );
        echo $this->_compiler->setOptions($options)->render();
    }

    /**
     * Renders the widget within an iframe.
     */
    public function frameAction()
    {
        // Iframe parameters
        $options = array();
        
        // Data
        $options['data']  = array();
        $ignoredParams = array('id', 'uwaUrl', 'ifproxyUrl', 'header', 'status');
        foreach ($_GET as $name => $value) {
            // ignore netvibes 'NV' & google 'upt' prefixs
            if (substr($name, 0, 4) == 'upt_' || substr($name, 0, 2) == 'NV') {
                continue;
            } 
            // support for google style preferences
            if (substr($name, 0, 3) == 'up_') {
                $name = substr($name, 3);
            }
            if (!in_array($name, $ignoredParams)) {
                // Should be avoided
                // Fix a problem when displaying the default webnote of netvibes for example
                $value = stripslashes($value);
                $options['data'][$name] = $value;
            }
        }

        // Properties - not yet used
        $options['properties'] = array();
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

        $this->_compiler->setOptions($options);

        // Rendering

        Zend_Layout::getMvcInstance()->enableLayout()->setLayout('frame');

        $this->view->bodyClass = 'moduleIframe';
        if (isset($options['chromeColor'])) {
            $this->view->bodyClass .= ' ' .  $options['chromeColor'] . '-module';
        }

        $this->view->headTitle( $this->_widget->getTitle() );

        foreach ($this->_compiler->getStylesheets() as $stylesheet) {
            $this->view->headLink()->appendStylesheet($stylesheet, 'screen');
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'text/html')
            ->appendBody( $this->_compiler->getHtmlBody() );
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
        
        header("Content-type: application/json");
        
        echo Zend_Json::encode($object);
    }
}
