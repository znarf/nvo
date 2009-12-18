/*
License:
    Copyright Netvibes 2006-2009.
    This file is part of UWA JS Runtime.

    UWA JS Runtime is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    UWA JS Runtime is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with UWA JS Runtime. If not, see <http://www.gnu.org/licenses/>.
*/

/*
    widgetLoaded : a true UWA widget is in use
*/
VistaModule = function( inFlyout )
{
    //is a true UWA widget loaded ?
    this.widgetLoaded = false;
    //is the gadget offline ?
    this.offline = false;
    //fix for endTransition
    this.sizeModified = -1;
    //is this class used in the flyout window ?
    this.inFlyout = inFlyout;
};

VistaModule.prototype =
{
    /*
        Creates the module with an url given by the compilation.xml file (online compilation)
        or directly with the file included in the gadget (offline compilation)
    */
    createCompiledModule: function()
    {
        var iconMetas = document.getElementsByTagName( 'link' );
        var icon = null;
        for( var i = 0; i < iconMetas.length && icon == null; i++ )
            if( iconMetas[i].rel == 'icon' )
                icon = iconMetas[i].href;

        if( icon == null || icon == '' )
            icon = 'img/faviconUWA.png';

        this.environment.html['icon'].innerHTML = '<img width="16" height="16" src="' + icon + '" />';

        this.refreshSize();

        this.loadOnlineIcon = (icon == null || icon == '');

        //we change the way to handle css for the widget
        var ExtWidget = {
            setCSS: function( css )
            {
                UWA.Utils.setCss(this.id, css, '#wrapper');
            }
        };
        UWA.extend(UWA.Widget.prototype, ExtWidget);

        //function to launch the module
        this.environment.load = function()
        {
            if( typeof UWA.Classes == 'undefined' || typeof UWA.Classes.CompiledModule == 'undefined' )
            {
                return;
            }
            this.clearPeriodical('loadenvironment');
            var options = { preferences:true, style:true, title:true, body:true, script: true, icon:this.vistaModule.loadOnlineIcon, metas:false };

            UWA.Classes.CompiledModule( this, options );

            this.vistaModule.Module = this.getModule();
            this.vistaModule.Module.debugMode = true;
            this.vistaModule.prefsFormCtrl = new Netvibes.UI.PrefsForm( { module: this.getModule() } );

            //the widget content will be resized every 500 ms
            if( this.vistaModule.inFlyout || (! System.Gadget.docked) )
                this.setPeriodical( 'refreshSize', function(){ this.refreshSize() }.bind( this.vistaModule ), 500 );

            this.vistaModule.moduleLoaded = true;
        };

        //the function will be called when the library will be loaded
        this.environment.setPeriodical('loadenvironment', this.environment.load.bind(this.environment), 200);
    },

    /*
        Loads the vista gadget
        Called by document.onload
        Determines if an UWA widget is compiled or not and launches the good function
    */
    load: function()
    {
        if( typeof UWA == 'undefined' )
        {
            this.showOffline();
            return;
        }

        //Modifies the look if the gadget is docked
        if(! this.inFlyout )
        {
            if( System.Gadget.docked )
            {
                //flyout
                System.Gadget.Flyout.file = 'flyout.html';
                document.body.onclick = this.showFlyout.bind( this );
                System.Gadget.Flyout.onShow = this.onShowFlyout.bind( this );
                System.Gadget.Flyout.onHide = this.onHideFlyout.bind( this );
                //size
                this.refreshSize();
                //background
                var bg = document.getElementById( 'vBackground' );
                bg.addImageObject( 'img/docked.png', 0, 0 );
            }

            //Settings
            System.Gadget.settingsUI = "settings.html";
            System.Gadget.onShowSettings = this.savePreferencesHTML.bind( this );
            System.Gadget.onSettingsClosed = this.settingsClosed.bind( this );

            //Gadget bindings
            System.Gadget.onDock = this.refresh.bind( this );
            System.Gadget.onUndock = this.refresh.bind( this );
        }

        //Proxies must have full urls

        //create the environment (customised for vista in load.js)
        this.environment = new UWA.Environment();

        this.environment.debugMode = false;

        this.environment.vistaModule = this;

        //refreshs the picture of the gadget
        this.endTransition();

        if( this.inFlyout )
            this.environment.setDelayed( "createCompiledModule", this.createCompiledModule.bind( this ), 500, this );
        else
            this.createCompiledModule();
    },

    /*
        Refreshs the size of the gadget (height only)
    */
    refreshSize: function()
    {
        if( (! this.offline) && ( this.inFlyout || (! System.Gadget.docked) ) )
        {
            if( document.body.style.height != (document.getElementById('vistaContent').offsetHeight + 'px') )
            {
                document.body.style.height = document.getElementById('vistaContent').offsetHeight + 'px';
                //fix a bug : the flyout loses focus when resized
                if( this.inFlyout )
                    this.environment.setDelayed( "takeBackFocus", function(){ self.focus() }, 300 );
            }
        }
        else
        {
            document.getElementById('vistaContent').style.width = '137px';
            document.getElementById('vistaContent').style.height = '63px';
            document.body.style.width = '137px';
            document.body.style.height = '63px';
        }
    },

    /*
        Refreshs the gadget
    */
    refresh: function()
    {
        this.beginTransition();
        window.location = window.location;
    },

    /*
        Refreshs the gadget when the settings are validated
    */
    settingsClosed: function( event )
    {
        if (event.closeAction == event.Action.commit)
            this.refresh()
    },

    /*
        Prepares the settings form and write it in a file that will be read
        by settings.html
    */
    savePreferencesHTML: function()
    {
        if( this.moduleLoaded )
        {
            //preferences of the module
            var prefsContent = this.prefsFormCtrl.getContent();
            if( prefsContent.childNodes.length != 0 )
            {
                var tmp = this.Module.createElement( 'div' );
                tmp.appendChild( prefsContent );
                var content = tmp.innerHTML;
                tmp = null;
            }
            else
            {
                content = '<p>' + _("No settings") + '</p>';
            }
        }
        else
        {
            content = '<p>' + _("No settings") + '</p>';
        }

        //Preferences are writtent in a temporary file in the directory of the widget
        var fileName = 'prefs_' + Math.ceil( Math.random() * 1000 ) + '.tmp';
        System.Gadget.Settings.writeString( 'modulePrefsFile', fileName );
        try
        {
            var fs = new ActiveXObject("Scripting.FileSystemObject");
            var newFile = fs.CreateTextFile( System.Gadget.path + "\\" + fileName, true);
            newFile.Write(content);
            newFile.Close();
        }
        catch(e)
        {
            //nothing
        }
    },

    /*
        Shows an error message if offline
        (only in online mode)
    */
    showOffline: function()
    {
        this.offline = true;
        this.refreshSize();
        var bg = document.getElementById( 'vBackground' );
        bg.addImageObject( 'img/docked.png', 0, 0 );
        document.getElementById( 'vistaContent' ).style.padding = '0';
        document.getElementById( 'vistaContent' ).innerHTML = '<p style="margin-left: 5px; margin-top: 5px;">Service unavailable</p>';
        this.beginTransition();
        setTimeout( 'System.Gadget.beginTransition(); window.location = window.location;', 5000 );
    },

    /*
        Shows or hide the flyout
    */
    showFlyout: function()
    {
        if(! System.Gadget.Flyout.show )
            System.Gadget.Flyout.show = true;
        else
            System.Gadget.Flyout.show = false;
    },

    /*
        Prepares the title to be updated by the flyout
    */
    onShowFlyout: function()
    {
        if( System.Gadget.Flyout.show )
        {
            this.environment.html['title'] = document.createElement('div');
            this.environment.html['title'].innerHTML = document.getElementById( 'moduleTitle' ).innerHTML;
            this.environment.setPeriodical( 'refreshTitleFlyout', function(){
                if( System.Gadget.Flyout.document && System.Gadget.Flyout.document.getElementById( 'moduleTitle' ) )
                    document.getElementById( 'moduleTitle' ).innerHTML = System.Gadget.Flyout.document.getElementById( 'moduleTitle' ).innerHTML;
            }, 200, true);
        }
    },

    /*
        Stops the update of the title from the flyout
    */
    onHideFlyout: function()
    {
        if(! System.Gadget.Flyout.show )
        {
            this.environment.clearPeriodical( 'refreshTitleFlyout' );
            document.getElementById( 'moduleTitle' ).innerHTML = this.environment.html['title'].innerHTML;
            this.environment.html['title'] = document.getElementById( 'moduleTitle' );
        }
    },

    /*
        Freezes the picture of the gadget
    */
    beginTransition: function()
    {
        System.Gadget.beginTransition();
    },

    /*
        Refreshs the frozen picture of the gadget
        2 fixes :
            - prevents the backgound to be corrupted
            - prevents a freeze if the size is unchanged
    */
    endTransition: function()
    {
        var transitionTime = 0.3;

        document.body.style.height = document.body.offsetHeight + 1;
        this.sizeModified = document.body.offsetHeight;

        System.Gadget.endTransition( System.Gadget.TransitionType.none, transitionTime );
        window.setTimeout( function(){
            if( this.sizeModified == document.body.offsetHeight )
                document.body.style.height = document.body.offsetHeight - 1;
            var bg = document.getElementById( 'vBackground' );
            bg.src = bg.src;
        }, transitionTime * 1000 + 300);
    }
};
