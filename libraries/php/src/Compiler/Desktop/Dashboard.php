<?php

require_once 'Compiler/Desktop.php';

/**
 * Apple Dashboard Widgets Compiler.
 */
class Compiler_Desktop_Dashboard extends Compiler_Desktop
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Dashboard2';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-dashboard.css';

    protected function buildArchive()
    {
        $dirname = $this->getNormalizedTitle();
        $dirname = preg_replace('/[^a-z0-9:;,?.()[]{}=@ _-]/i', '', $dirname) . '.wdgt/';

        // Add the widget skeleton to the archive
        $ressourcesDir = Zend_Registry::get('uwaRessourcesDir');
        if (!is_readable($ressourcesDir)) {
            throw new Exception('UWA ressources directory is not readable.');
        }
        $this->addDirToZip($ressourcesDir . 'dashboard', $dirname);

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToZip($richIcon, $dirname . 'Icon.png');
        }

        $this->_zip->addFromString($dirname . 'index.html', $this->getHtml() );

        $this->_zip->addFromString($dirname . 'Info.plist', $this->_getXmlManifest() );
    }

    private function _getXmlManifest($options)
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();
        $identifier = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());

        $options = array(
            'AllowNetworkAccess' => true,
            'AllowInternetPlugins' => true,
            'MainHTML' => 'index.html',
            'Width' => 358,
            'Height' => 600,
            'CloseBoxInsetX' => 15,
            'CloseBoxInsetY' => 5,
            'CFBundleIdentifier' => 'com.netvibes.widget.' . $identifier,
            'CFBundleDisplayName' => $title,
            'CFBundleName' => $title,
            'CFBundleVersion' => isset($metas['version']) ? $metas['version'] : '1.0');

        $l = array();

        $l[] = '<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';

        $l[] = '<plist version="1.0">';
        $l[] = '<dict>';

        foreach ($options as $key => $value) {
            $l[] = '<key>' . htmlspecialchars($key) . '</key>';
            if (is_bool($value)) {
                $l[] = $value ? '<true/>' : '<false/>';
            } elseif (is_int($value)) {
                $l[] = '<integer>' . $value . '</integer>';
            } else {
                $l[] = '<string>' . htmlspecialchars($value) . '</string>';
            }
        }

        $l[] = '</dict>';
        $l[] = '</plist>';

        return implode("\n", $l);
    }

    public function getHtml()
    {
        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        foreach ($this->_getStylesheets() as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '"/>';
        }
        
        $l[] = '</head>';
        $l[] = '<body>';

        $l[] = $this->_getHtmlBody();
        
        $l[] = $this->_getJavascriptConstants();
        
        foreach ($this->_getJavascripts() as $script) {
            $l[] = "<script type='text/javascript' src='" . $script . "' charset='utf-8'/>";
        }
        
        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getScript();
        $l[] = '</script>';

        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }
    
    private function _getHtmlBody()
    {
        $l = array();
        
        $l[] = '<div class="module" id="wrapper">';
        $l[] =   $this->_getHtmlHeader();
        $l[] =   '<div id="contentWrapper">';
        $l[] =     '<div class="moduleContent" id="moduleContent">';
        $l[] =       $this->_widget->getBody();
        $l[] =     '</div>';
        $l[] =     $this->_getHtmlStatus();
        $l[] =   '</div>';
        $l[] =   '<div class="moduleFooter" id="moduleFooter"></div>';
        $l[] = '</div>';
        
        return implode("\n", $l);
    }
    
    private function _getScript()
    {
        $l = array();
        
        $proxies = array(
            'ajax' => Zend_Registry::get('proxyEndpoint') . '/ajax',
            'feed' => Zend_Registry::get('proxyEndpoint') . '/feed'
        );
        
        $l[] = sprintf('UWA.proxies = %s;', Zend_Json::encode($proxies));

        $l[] = "var id = window.widget ? 'dashboard-' + widget.identifier : 'dashboard';";
        $l[] = "Environments[id] = new UWA.Environment();";
        $l[] = "Widgets[id] = Environments[id].getModule();";
        $l[] = sprintf('Widgets[id].uwaUrl = %s;', Zend_Json::encode($this->_widget->getUrl()));
        $l[] = "UWA.script(Widgets[id]);";
        $l[] = "Environments[id].launchModule();";
        
        return implode("\n", $l);
    }

    protected function _getJavascripts()
    {
        $javascripts = parent::_getJavascripts();
        if (Zend_Registry::get('useCompressedJs')) {
            $javascripts[] = Zend_Registry::get('uwaJsDir') . 'UWA_Controls_PrefsForm.js';
        } else {
            $javascripts[] = Zend_Registry::get('uwaJsDir') . 'Controls/PrefsForm.js';
        }
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleInfoButton.js';
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleAnimator.js';
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleButton.js';
        return $javascripts;
    }

    public function getFileName()
    {
        $filename = $this->getNormalizedTitle();
        $extension = 'wdgt.zip';
        if (!empty($filename)) {
            return $filename . '.' . $extension;
        } else {
            return 'DashboardWidget' . '.' . $extension;
        }
    }

    public function getNormalizedTitle()
    {
        return $this->_widget->getTitle();
    }

    public function getFileMimeType()
    {
        return 'application/zip';
    }
}
