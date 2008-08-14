/*
License:
  Copyright (c) 2005-2008 Netvibes (http://www.netvibes.org/).

  This file is part of Netvibes Widget Platform.

  Netvibes Widget Platform is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Netvibes Widget Platform is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License
  along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
*/

UWA.Controls.PrefsForm = function(options) {

    /* Property: module

    *Module*: the module using the current Preference form.
    */
    this.widget = options.module;

    /* Property: displayButton

    *Type*: ..
    */
    this.displayButton = options.displayButton;
}

UWA.Controls.PrefsForm.prototype.controls = {}

UWA.Controls.PrefsForm.prototype.controls['default'] = function(pref) {
  var input = this.widget.createElement('input', {
    'id'    : 'm_' + this.widget.id + '_' + pref.name,
    'type'  : 'text',
    'name'  : pref.name,
    'value' : this.widget.getValue(pref.name) || ''
  });
  return input;
}

UWA.Controls.PrefsForm.prototype.controls['boolean'] = function(pref) {
  var input = this.widget.createElement('input', {
    'id'   : 'm_' + this.widget.id + '_' + pref.name,
    'type' : 'checkbox',
    'name' : pref.name
  });
  if (this.widget.getBool(pref.name) === true) {
    input.setAttribute('checked', 'checked');
    input.defaultChecked = true; // for IE
  }
  if (pref.onchange) {
    input.onclick = ( function(e) {
      var sender = (e.target || e.srcElement);
      if(sender.checked == true) {
        this.widget.setValue(pref.name, 'true');
      } else {
        this.widget.setValue(pref.name, 'false');
      }
      this.widget.callback(pref.onchange);
    } ).bindAsEventListener(this);
  }
  return input;
}

UWA.Controls.PrefsForm.prototype.controls['password'] = function(pref) {
  var input = this.widget.createElement('input', {
    'id'    : 'm_' + this.widget.id + '_' + 'pass',
    'type'  : 'password',
    'name'  : 'pass',
    'value' : ''
  });
  return input;
}

UWA.Controls.PrefsForm.prototype.controls['textarea'] = function(pref) {
  var input = this.widget.createElement('textarea', {
    'id'   : 'm_' + this.widget.id + '_' + pref.name,
    'name' : pref.name
  }).setText( this.widget.getValue(pref.name) || '' );
  return textarea;
}

UWA.Controls.PrefsForm.prototype.controls['range'] = function(pref) {   
  var select = this.widget.createElement('select', {
    'id'   : 'm_' + this.widget.id + '_' + pref.name,
    'name' : pref.name
  });
  if (parseInt(pref.step) > 0) {
    for (var i = parseInt(pref.min); i <= parseInt(pref.max); i += parseInt(pref.step)) {
      var option = this.widget.createElement('option', { 'value': i }).setText("" + i);
      if (this.widget.getValue(pref.name) == i) {
        option.setAttribute('selected', 'selected');
      } 
      select.appendChild(option);
    }
  }
  if (pref.onchange) {
    select.onchange = ( function(e) {
      var sender = (e.target || e.srcElement);
      this.widget.setValue(pref.name, sender.value)
      this.widget.callback(pref.onchange);
    } ).bindAsEventListener(this);
  }
  return select;
}

UWA.Controls.PrefsForm.prototype.controls['list'] = function(pref) {   
  var select = this.widget.createElement('select', {
    'id'   :  'm_' + this.widget.id + '_' + pref.name,
    'name' :  pref.name
  });
  for (var i = 0; i < pref.options.length; i++) {
    var option = pref.options[i];
    option.label = option.label || option.value;
    var optionElement = this.widget.createElement('option').setText(option.label).inject(select);
    optionElement.value = option.value;
    if (this.widget.getValue(pref.name) == option.value) {
      optionElement.setAttribute('selected', 'selected');
    }
  }
  if (pref.onchange) {
    select.onchange = ( function(e) {
      var sender = (e.target || e.srcElement);
      this.widget.setValue(pref.name, sender.value)
      this.widget.callback(pref.onchange);
    } ).bindAsEventListener(this);
  }
  return select;
}

/* Method: build

Gets the content of the preference form, for display.

Parameters:
* None.

Returns:
* String: the HTML version of the preference form.
*/
UWA.Controls.PrefsForm.prototype.build = function() {
    
  var widget = this.widget;

  var form = this.form = widget.createElement("form");
  var table = widget.createElement("table").addClassName("formTable").setStyle('width', '100%').inject(form);
  var tbody = widget.createElement("tbody").inject(table);

  for (var i = 0; i < widget.preferences.length; i++) {
    var pref = widget.preferences[i];
    if (pref.type == 'hidden') {
      continue;
    }
    var tr = widget.createElement("tr").inject(tbody);
    var label = _( (pref.label || pref.name) + ':' );
    var tdl = widget.createElement("td").inject(tr);
    var label = widget.createElement("label", {
      'for': widget.id + '_' + pref.name 
    }).setText(label).inject(tdl);
    if (typeof this.controls[pref.type] == 'undefined') {
      pref.type = 'default';
    }
    var control = this.controls[pref.type].bind(this)(pref);
    widget.createElement("td").setContent(control).inject(tr);
  }

  if (typeof this.displayButton == 'undefined' || this.displayButton === true) {
    var tr = widget.createElement("tr").inject(tbody);
    var tds = widget.createElement("td", {'colSpan' : 2}).inject(tr);
    widget.createElement('input', {
      'type'  : 'submit',
      'value' :  _("Ok")
    }).addClassName('buttonClean').inject(tds);
  }

  form.onsubmit = ( function() {
    var callback = ( function() { this.callback('endEdit') } ).bind(this.widget);
    this.saveValues();
    // force to saveValues immediatly and add a callback when it's done
    this.widget.saveValues(callback);
    return false;
  } ).bindAsEventListener(this);

  return form;

}

UWA.Controls.PrefsForm.prototype.getContent = UWA.Controls.PrefsForm.prototype.build;

UWA.Controls.PrefsForm.prototype.saveValues = function(formElement) {
    
  var widget = this.widget;
  var form = formElement || this.form
  var formElements = UWA.Form.getElements(form);

  for (var j = 0; j < formElements.length; j++) {
    var el = formElements[j];
    switch (el.type) {
      case 'submit':
        break;
      case 'password':
        if (el.value != '') {
          widget.setValue(el.name, el.value)
        }
        break;
      case 'checkbox':
        if (el.checked) {
          widget.setValue(el.name, 'true');
        } else {
          widget.setValue(el.name, 'false');
        }
        break;  
      case 'radio':
        if (el.checked) {
          widget.setValue(el.name, el.value);
        }
        break;
      default :
       widget.setValue(el.name, el.value);
      break;
    }
  }

}
