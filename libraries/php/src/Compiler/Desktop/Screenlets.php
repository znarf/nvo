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
final class Compiler_Desktop_Screenlets extends Compiler_Desktop
{
    /**
     * Archive Format of the widget.
     *
     * @var string
     */
    protected $archiveFormat = 'Tar';

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
     * Compiler Name.
     *
     * @var string
     */
    protected $_platform = 'frame';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'tar.gz';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-gzip';

    protected function buildArchive()
    {

		$identifier = $this->getNormalizedTitle();
        $dirname = preg_replace('/[^a-z0-9:;,?.()[]{}=@ _-]/i', '', $identifier) . '/';

        // Add the widget skeleton to the archive
        $ressourcesDir = Zend_Registry::get('uwaRessourcesDir');
        if (!is_readable($ressourcesDir)) {
            throw new Exception('UWA ressources directory is not readable.');
        }
        $this->addDirToArchive($ressourcesDir . 'screenlets', $dirname);

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToArchive($richIcon, $dirname . 'icon.png');
        }

        // add widget html content
        $this->addFileFromStringToArchive($dirname . 'widget.html', $this->getHtml());

        // render screenlets python script
        $this->addFileFromStringToArchive($dirname . $identifier . 'Screenlet.py', $this->_getScreenletScript() );
    }


    protected function getHtml()
    {
        $compiler = Compiler_Factory::getCompiler($this->_platform, $this->_widget);

        $options = array(
            'displayHeader' => 1,
            'displayStatus' => 1,
            'properties'    => array(
                'id' => time(),
            ),
        );

        $compiler->setOptions($options);

        return $compiler->render();
    }

	protected function _getScreenletScriptOptions()
    {
        // @todo

		return null;
    }

    protected function _getScreenletScript()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $identifier = $this->getNormalizedTitle();

        $templateVars = array(
            '{widgetName}'          => self::stringToAscii($title),
            '{widgetClassName}'     => $identifier,
            '{widgetDescription}'   => self::stringToAscii($metas['description']),
            '{widgetVersion}'       => $metas['apiVersion'],
            '{widgetAuthor}'        => self::stringToAscii($metas['author']),
            '{widgetHeight}'        => $this->_height,
            '{widgetWidth}'         => $this->_width,
			'{widgetOptions}'       => $this->_getScreenletScriptOptions(),
        );

        $templateScript = <<<EOF
#!/usr/bin/env python

# {widgetClassName}Screenlet
# Author: {widgetAuthor}

import screenlets
from screenlets import sensors
from screenlets import DefaultMenuItem
from screenlets.options import BoolOption, IntOption, ColorOption, StringOption
import cairo
import gtk
import gobject
import commands
import sys
import os
from os import system
from screenlets import sensors

#########WORKARROUND FOR GTKOZEMBED BUG BY WHISE################
myfile = '{widgetClassName}Screenlet.py'
mypath = sys.argv[0][:sys.argv[0].find(myfile)].strip()

if sys.argv[0].endswith(myfile): # Makes Shure its not the manager running...
		# First workarround
		c = None
		workarround =  "python "+ sys.argv[0] + " &"
		a = str(commands.getoutput('whereis firefox')).replace('firefox: ','').split(' ')
		for b in a:
			if os.path.isfile(b + '/run-mozilla.sh'):
				c = b + '/run-mozilla.sh'
				workarround = c + " " + sys.argv[0] + " &"


		if c == None:
			# Second workarround
			print 'First workarround didnt work let run a second manual workarround'
			if str(sensors.sys_get_distrib_name()).lower().find('ubuntu') != -1: # Works for ubuntu 32
				workarround = "export LD_LIBRARY_PATH=/usr/lib/firefox \\n export MOZILLA_FIVE_HOME=/usr/lib/firefox \\n python "+ sys.argv[0] + " &"
			elif str(sensors.sys_get_distrib_name()).lower().find('debian') != -1: # Works for debian 32 with iceweasel installed
				workarround = "export LD_LIBRARY_PATH=/usr/lib/iceweasel \\n export MOZILLA_FIVE_HOME=/usr/lib/iceweasel \\n python " + sys.argv[0] + " &"
			elif str(sensors.sys_get_distrib_name()).lower().find('suse') != -1: # Works for suse 32 with seamonkey installed
				workarround = "export LD_LIBRARY_PATH=/usr/lib/seamonkey \\n export MOZILLA_FIVE_HOME=/usr/lib/seamonkey \\n python "+ sys.argv[0] + " &"
				print 'Your running suse , make shure you have seamonkey installed'
			elif str(sensors.sys_get_distrib_name()).lower().find('fedora') != -1: # Works for fedora 32 with seamonkey installed
				workarround = "export LD_LIBRARY_PATH=/usr/lib/seamonkey \\n export MOZILLA_FIVE_HOME=/usr/lib/seamonkey \\n python "+ sys.argv[0] + " &"
				print 'Your running fedora , make shure you have seamonkey installed'


		if os.path.isfile("/tmp/"+ myfile+"running"):
			os.system("rm -f " + "/tmp/"+ myfile+"running")

		else:
			if workarround == "python "+ sys.argv[0] + " &":
				print 'No workarround will be applied to your system, this screenlet will probably not work properly'
			os.system (workarround)
			fileObj = open("/tmp/"+ myfile+"running","w") #// open for for write
			fileObj.write('gtkmozembed bug workarround')

			fileObj.close()
			sys.exit()
else:
	pass
try:
	import gtkmozembed
except:
	if sys.argv[0].endswith(myfile):screenlets.show_error(None,"You need Gtkmozembed to run this Screenlet, please install \"python-gnome2-extras\" package.")
	else: print "You need Gtkmozembed to run this Screenlet, please install \"python-gnome2-extras\" package."
#########WORKARROUND FOR GTKOZEMBED BUG BY WHISE################

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
		{widgetOptions}
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

        return $script;

    }

    /**
     * Convert a string to ascii
     *
     * @param string $string a little string
     * @return array $array value
     */
    static protected function stringToAscii($string)
    {
        $transliteration =  array(
        "À" => "A","Á" => "A","Â" => "A","Ã" => "A","Ä" => "A",
        "Å" => "A","Æ" => "A","Ā" => "A","Ą" => "A","Ă" => "A",
        "Ç" => "C","Ć" => "C","Č" => "C","Ĉ" => "C","Ċ" => "C",
        "Ď" => "D","Đ" => "D","È" => "E","É" => "E","Ê" => "E",
        "Ë" => "E","Ē" => "E","Ę" => "E","Ě" => "E","Ĕ" => "E",
        "Ė" => "E","Ĝ" => "G","Ğ" => "G","Ġ" => "G","Ģ" => "G",
        "Ĥ" => "H","Ħ" => "H","Ì" => "I","Í" => "I","Î" => "I",
        "Ï" => "I","Ī" => "I","Ĩ" => "I","Ĭ" => "I","Į" => "I",
        "İ" => "I","Ĳ" => "IJ","Ĵ" => "J","Ķ" => "K","Ľ" => "K",
        "Ĺ" => "K","Ļ" => "K","Ŀ" => "K","Ł" => "L","Ñ" => "N",
        "Ń" => "N","Ň" => "N","Ņ" => "N","Ŋ" => "N","Ò" => "O",
        "Ó" => "O","Ô" => "O","Õ" => "O","Ö" => "Oe","Ø" => "O",
        "Ō" => "O","Ő" => "O","Ŏ" => "O","Œ" => "OE","Ŕ" => "R",
        "Ř" => "R","Ŗ" => "R","Ś" => "S","Ş" => "S","Ŝ" => "S",
        "Ș" => "S","Š" => "S","Ť" => "T","Ţ" => "T","Ŧ" => "T",
        "Ț" => "T","Ù" => "U","Ú" => "U","Û" => "U","Ü" => "Ue",
        "Ū" => "U","Ů" => "U","Ű" => "U","Ŭ" => "U","Ũ" => "U",
        "Ų" => "U","Ŵ" => "W","Ŷ" => "Y","Ÿ" => "Y","Ý" => "Y",
        "Ź" => "Z","Ż" => "Z","Ž" => "Z","à" => "a","á" => "a",
        "â" => "a","ã" => "a","ä" => "ae","ā" => "a","ą" => "a",
        "ă" => "a","å" => "a","æ" => "ae","ç" => "c","ć" => "c",
        "č" => "c","ĉ" => "c","ċ" => "c","ď" => "d","đ" => "d",
        "è" => "e","é" => "e","ê" => "e","ë" => "e","ē" => "e",
        "ę" => "e","ě" => "e","ĕ" => "e","ė" => "e","ƒ" => "f",
        "ĝ" => "g","ğ" => "g","ġ" => "g","ģ" => "g","ĥ" => "h",
        "ħ" => "h","ì" => "i","í" => "i","î" => "i","ï" => "i",
        "ī" => "i","ĩ" => "i","ĭ" => "i","į" => "i","ı" => "i",
        "ĳ" => "ij","ĵ" => "j","ķ" => "k","ĸ" => "k","ł" => "l",
        "ľ" => "l","ĺ" => "l","ļ" => "l","ŀ" => "l","ñ" => "n",
        "ń" => "n","ň" => "n","ņ" => "n","ŉ" => "n","ŋ" => "n",
        "ò" => "o","ó" => "o","ô" => "o","õ" => "o","ö" => "oe",
        "ø" => "o","ō" => "o","ő" => "o","ŏ" => "o","œ" => "oe",
        "ŕ" => "r","ř" => "r","ŗ" => "r","ś" => "s","š" => "s",
        "ť" => "t","ù" => "u","ú" => "u","û" => "u","ü" => "ue",
        "ū" => "u","ů" => "u","ű" => "u","ŭ" => "u","ũ" => "u",
        "ų" => "u","ŵ" => "w","ÿ" => "y","ý" => "y","ŷ" => "y",
        "ż" => "z","ź" => "z","ž" => "z","ß" => "ss","ſ" => "ss");

        return str_replace( array_keys( $transliteration ), array_values( $transliteration ), $string);
    }

	public function getFileName()
    {
        return $this->getNormalizedTitle() . '.' . $this->_extension;
    }

    public function getNormalizedTitle()
    {
        $filename = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());
        if (!empty($filename)) {
            return $filename;
        } else {
            return 'Widget';
        }
    }

	public function getFileMimeType()
    {
        return $this->_mimeType;
    }

}
