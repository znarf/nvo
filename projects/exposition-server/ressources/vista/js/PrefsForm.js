/*
Copyright 2005-2007 Netvibes Ltd
All rights reserved.
Author : François HODIERNE
*/

if (typeof Netvibes == "undefined" || !Netvibes) var Netvibes = {};

if (typeof Netvibes.UI == "undefined" || !Netvibes.UI) Netvibes.UI = {};

Netvibes.UI.PrefsForm = function(params)
{
    /* Property: module

    *Module*: the module using the current Preference form.
    */
    this.module = params.module;

}

Netvibes.UI.PrefsForm.prototype =
{

  /* Property: controls

  *Collection{String->Function}*: associates the name of a control with the method creating the control.

  Those controls are used to give a "type" to the form widgets.

  Available controls are:
  * default: generates a standard text input,
  * boolean: generates a checkbox,
  * password: generates a password input,
  * textarea: generates a text area,
  * range: generates a dropdown list.

  */
    controls:
    {

        'default': function(pref)
        {
            var value = this.module.getValue(pref.name);
            if(!value || value == 'null') value = ''; // why string null ?
            var input = this.module.createElement('input');
            input.setAttribute('id', 'm_' + pref.name);
            input.type = "text";
            input.value = value;
            input.setAttribute( 'onchange', 'vistaPrefs.saveTmpValue( "' + pref.name + '", this.value );' );
            return input;
        },

        'boolean': function(pref)
        {
            var input = this.module.createElement('input');
            input.setAttribute('id', 'm_' + pref.name);
            input.type = "checkbox";
            if(this.module.getValue(pref.name) == 'true')
            {
                input.setAttribute('checked', 'checked');
                input.defaultChecked = true; // for IE
            }
            input.setAttribute( 'onchange', 'if( this.checked ) var value = "true"; else var value = "false"; vistaPrefs.saveTmpValue( "' + pref.name + '", value );' );
            return input;
        },

        'password': function(pref)
        {
            var value = this.module.getValue(pref.name);
            if(!value || value == 'null') value = ''; // why string null ?
            var input = this.module.createElement('input');
            input.type = "password";
            input.value = value;
            input.setAttribute( 'onchange', 'vistaPrefs.saveTmpValue( "' + pref.name + '", this.value );' );
            return input;
        },

        'textarea': function(pref)
        {
            if(this.module.getValue(pref.name)) var value = this.module.getValue(pref.name); else var value = '';
            var textarea = '<textarea id="m_' + name + '" name="' + pref.name + '" onchange="vistaPrefs.saveTmpValue( \'' + pref.name + '\', this.value );">' + value + '</textarea> ';
            return textarea;
        },

        'range': function(pref)
        {
            var select = this.module.createElement('select');
            select.setAttribute('id', 'm_' + pref.name);
            if (parseInt(pref.step) > 0)
            {
                for(var i=parseInt(pref.min); i<=parseInt(pref.max); i+=parseInt(pref.step))
                {
                    var option = this.module.createElement('option');
                    if(this.module.getValue(pref.name) == i) option.setAttribute('selected', 'selected');
                    option.value = i;
                    option.setText(i);
                    select.appendChild(option);
                }
            }
            select.setAttribute( 'onchange', 'vistaPrefs.saveTmpValue( "' + pref.name + '", this.options[this.selectedIndex].value );' );
            return select;
        },

        'list': function(pref)
        {
            var select = this.module.createElement('select');
            select.setAttribute('id', 'm_' + pref.name);
            for(var i=0; i<pref.options.length; i++)
            {
                var option = this.module.createElement('option');
                if(this.module.getValue(pref.name) == pref.options[i].value) option.setAttribute('selected', 'selected');
                option.value = pref.options[i].value;
                option.setText( (pref.options[i].label ? pref.options[i].label : pref.options[i].value ) );
                select.appendChild(option);
            }
            select.setAttribute( 'onchange', 'vistaPrefs.saveTmpValue( "' + pref.name + '", this.options[this.selectedIndex].value );' );
            return select;
        },

        'color': function(pref)
        {
            if(typeof pref.colors == "undefined")
                pref.colors = ['white', 'yellow', 'green', 'red', 'blue', 'orange'];
            var colorSelector = this.module.createElement('div');
            colorSelector.setAttribute('id', 'm_' + pref.name);
            colorSelector.addClassName('postItcolorSelection');
            for(i=0; i<pref.colors.length;i++)
            {
                var color = this.module.createElement('div');
                //color.addClassName(colors[i]);
                color.setStyle('background-color', pref.colors[i]);
                color.setAttribute( 'onclick', 'vistaPrefs.saveTmpValue( "' + pref.name + '", this.style.backgroundColor );' );
                colorSelector.appendChild(color);
            };
            return colorSelector;
        }

    },

    /* Method: getContent

    Gets the content of the preference form, for display.

    Parameters:
    * None.

    Returns:
    * String: the HTML version of the preference form.
    */
    getContent: function()
    {
        var div = this.module.createElement("div");
        div.setStyle('width', '100%');

        for(var i = 0; i < this.module.preferences.length; i++)
        {
            var pref = this.module.preferences[i];
            if(pref.type == 'hidden') continue;

            if(!pref.label) pref.label = pref.name;
            var label = this.module.createElement("label");
            label.setAttribute('for', 'm_' + pref.name);
            label.setText( _(pref.label+':') );

            if(!this.controls[pref.type]) pref.type = 'default';
            var control = this.controls[pref.type].bind(this)(pref);

            div.appendChild( label );
            div.appendChild( control );
        }

        return div;
    }

}