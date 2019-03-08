
Event.observe(window, 'load', uzenoPSFLoader);

function uzenoPSFLoader(evt) {

    var r = Math.floor(Math.random()*100);
    includeJS('skin/classic/module-naplo/javascript/uzeno/postas.js?'+r);

/*
    $('cimzettTipus').observe('change', function(event) {

	var someNodeList = $(Event.element(event)).getElementsByTagName('option');

	$A(someNodeList).each(function(node){
		if (node.selected==true) $(node.value+'Id').show();
		else $(node.value+'Id').hide();
	});

    });
*/
  function checkCR(evt) {
    var evt  = (evt) ? evt : ((event) ? event : null);
    var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
    if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
  }
  document.onkeypress = checkCR;
/*
var a = document.createElement('script');
a.setAttribute('type', 'text/javascript');
a.setAttribute('src', 'skin/classic/module-naplo/javascript/uzeno/postas.js');
a.insert(document.head);
*/
}
