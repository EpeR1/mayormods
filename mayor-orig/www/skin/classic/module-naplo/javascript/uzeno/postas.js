
Event.observe(document.body, 'mayor:change',  uzenoCheck);
Event.observe(document.body, 'change', uzenoCheck);

function uzenoCheck(evt) {

	Event.observe('postazoButton', 'click', disableButton);

        var element = $(Event.element(evt));
	var thisid = (element.getAttribute('id'));
	switch(thisid) {
	    case 'pattern':
		$('postazoButton').hide();
		$('postazoTxt').hide();
		$('cimzett').selectedIndex=0;
		$('cimzett').disable();
		break;
	    case 'postazoButton': // ezt nem kapja el, nem change esemény
		$('postazoButton').disabled=true;
		$('postazoHash').setValue('submit');
		break;
	    case 'cimzett':
//		$('shTipus').update($F('cimzett'));
		$('cimzett').setAttribute('title',$F('cimzett'));
		break;
	    case 'postazoTxt':
		break;
	    default:
		break;
	}

	if ($('cimzett').selectedIndex!=0 && $('cimzett').value!='') {
	    $('postazoButton').show();
	    $('postazoTxt').show().focus();
	} else {
	    $('postazoButton').hide();
	    $('postazoTxt').hide();
	}

    //Element.addClassName($('uzenoPostazo'), 'csoport');

}

function disableButton(evt) {
    var element = $(Event.element(evt));
    if ($('postazoTxt').value!='') {
	var element = $(Event.element(evt));
	$('postazoHash').setValue('submit');
	$('postazoButton').disabled=true;
	element.up('form').submit();
    } else {
	Event.stop(evt);
    }
}