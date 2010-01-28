mywidget.onFirefoxLoad = function() {
  document.getElementById("contentAreaContextMenu")
          .addEventListener("popupshowing", function (e){ this.showFirefoxContextMenu(e); }, false);
};

mywidget.showFirefoxContextMenu = function(event) {
  // show or hide the menuitem based on what the context menu is on
  // see http://kb.mozillazine.org/Adding_items_to_menus
  document.getElementById("context-mywidget").hidden = gContextMenu.onImage;
};

window.addEventListener("load", function(e) { mywidget.onFirefoxLoad(e); }, false);
