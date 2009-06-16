<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Lib.
 *
 * Exposition PHP Lib is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exposition PHP Lib is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Lib.  If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'Compiler/Desktop/W3c.php';

/**
 * Apple Dashboard Widgets Compiler.
 */
final class Compiler_Desktop_Screenlets extends Compiler_Desktop_W3c
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Frame';

    /**
     * Width of the widget.
     *
     * @var string
     */
    protected $_width = 330;

    /**
     * Height of the widget.
     *
     * @var string
     */
    protected $_height = 370;

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform = 'frame';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iframe.css';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'screenlets.zip';

    protected function buildArchive()
    {

        $identifier = preg_replace('/[^a-z0-9]/i', '', $this->getNormalizedTitle());
        $dirname = preg_replace('/[^a-z0-9:;,?.()[]{}=@ _-]/i', '', $identifier) . '/';

        // Add the widget skeleton to the archive
        $ressourcesDir = Zend_Registry::get('uwaRessourcesDir');
        if (!is_readable($ressourcesDir)) {
            throw new Exception('UWA ressources directory is not readable.');
        }
        $this->addDirToZip($ressourcesDir . 'screenlets', $dirname);

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToZip($richIcon, $dirname . 'icon.png');
        }

        // add widget html content
        $this->_zip->addFromString($dirname . 'widget.html', $this->getHtml());

        // render screenlets python script
        $this->_zip->addFromString($dirname . $identifier . 'Screenlet.py', $this->_getScreenletScript() );
    }

    protected function getHtml()
    {
        $compiler = Compiler_Factory::getCompiler('frame', $this->_widget);
        $compiler->setOptions(array(
            'displayHeader'  => 1,
            'displayStatus'  => 1,
        ));

        return $compiler->render();
    }

    protected function _getXmlManifest()
    {
        // nothing todo..
    }

    protected function _getScreenletScript()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $identifier = preg_replace('/[^a-z0-9]/i', '', $this->getNormalizedTitle());

        $templateVars = array(
            '{widgetName}'          => $title,
            '{widgetClassName}'     => $identifier,
            '{widgetDescription}'   => $metas['description'],
            '{widgetVersion}'       => $metas['apiVersion'],
            '{widgetAuthor}'        => $metas['author'],
            '{widgetHeight}'        => $this->_height,
            '{widgetWidth}'         => $this->_width,
        );

        $templateScript = <<<EOF
#!/usr/bin/env python

# {widgetClassName}Screenlet
# Author: {widgetAuthor}

import screenlets
from screenlets.options import StringOption
import cairo
import gtk
import sys
import os
import commands
from os import system

#########WORKARROUND FOR GTKOZEMBED BUG################

if sys.argv[0].endswith('{widgetClassName}Screenlet.py'):
	if commands.getoutput("lsb_release -is") == 'Ubuntu':
		mypath = sys.argv[0][:sys.argv[0].find('{widgetClassName}Screenlet.py')].strip()
		if os.path.isfile('/tmp/screenlets/WidgetRunning'):
			os.system("rm -f /tmp/screenlets/WidgetRunning")

		else:
			os.system ("export LD_LIBRARY_PATH=/usr/lib/firefox \\n export MOZILLA_FIVE_HOME=/usr/lib/firefox \\n python  "+ sys.argv[0] + " &")
			fileObj = open('/tmp/screenlets/WidgetRunning',"w") #// open for for write
			fileObj.write('gtkmozembed bug workarround')

			fileObj.close()
			exit()
else:
	pass
try:
	import gtkmozembed
except:
	print 'You dont have gtkmozembed , please install python gnome extras'


######################################################

class {widgetClassName}Screenlet (screenlets.Screenlet):

    """{widgetDescription}"""

    __name__    = '{widgetName}'
    __version__ = '{widgetVersion}'
    __author__  = '{widgetAuthor}'
    __desc__    = __doc__

    base_dir    = sys.argv[0][:sys.argv[0].find('{widgetClassName}Screenlet.py')].strip()
    box         = False
    box         = gtk.VBox(False, 0)
    box_width   = {widgetWidth}
    box_height  = {widgetHeight}
    box_border  = 20
    moz         = False
    moz_width   = box_width - box_border * 2 #300
    moz_height  = box_height - box_border * 2 #340
    url_home    = 'widget.html'

    def __init__ (self, **keyword_args):
        screenlets.Screenlet.__init__(self, width=self.box_width, height=self.box_height, uses_theme=True, is_widget=False, is_sticky=True, **keyword_args)
        self.theme_name = 'BlackTinyBorder'
        self.add_menuitem('nav_reload', 'Reload')
        self.add_default_menuitems()
        self.add_menuitem('url_about', 'About {widgetName}')
        self.add_options_group('{widgetName}', '{widgetName} Options.')
        self.add_option(StringOption('{widgetClassName}', 'url_home', str(self.url_home), 'Home page', 'Home page'), realtime=False)
        if hasattr(gtkmozembed, 'set_profile_path'):
            gtkmozembed.set_profile_path(self.base_dir, 'mozilla')
        else:
            gtkmozembed.gtk_moz_embed_set_profile_path(self.base_dir, 'mozilla')

        self.box = gtk.VBox(False, 0)
        if self.box != None:
            self.box.set_border_width(self.box_border)
            #self.box.set_uposition(0, 0)
            self.box.set_size_request(self.moz_width, self.moz_height)
            self.moz = gtkmozembed.MozEmbed()
            self.moz.set_size_request(self.moz_width, self.moz_height)
            self.box.pack_start(self.moz, False, False, 0)
            self.box.set_uposition(1,0)
            self.window.add(self.box)
            self.window.show_all()
            self.moz.load_url(self.base_dir + self.url_home)

    def __setattr__ (self, name, value):
        screenlets.Screenlet.__setattr__(self, name, value)
        if name == 'url_home':
            self.__dict__[name] = value
            self.moz.load_url(value)
            self.redraw_canvas()

    def on_menuitem_select (self, id):
        if id == 'nav_goback':
            self.moz.go_back()
        elif id == 'nav_goforward':
            self.moz.go_forward()
        elif id == 'nav_reload':
            self.moz.reload(True)
        elif id == 'url_home':
            self.moz.load_url(self.url_home)

    def on_draw (self, ctx):
        ctx.scale(self.scale, self.scale)
        if self.theme:
            self.theme['bg.svg'].render_cairo(ctx)
        else:
            ctx.set_source_rgba(0, 0, 0, 0.7)
            ctx.rectangle(0, 0, self.box_width, self.box_height)
            ctx.fill()

    def on_draw_shape (self, ctx):
        if self.theme:
            self.on_draw(ctx)

if __name__ == '__main__':
    import screenlets.session
    screenlets.session.create_session({widgetClassName}Screenlet)
EOF;

        $script = str_replace( array_keys( $templateVars ), array_values( $templateVars ), $templateScript);


        echo $script;
        die();

        return $script;

    }

    protected function _getScreenletScriptOptions()
    {
        // @todo
    }
}
