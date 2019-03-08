
    Event.observe(window, 'load', myPSFLoader, false);

    hideOlOl = function() {
	$$('ol ol.negativ, ol ol.semleges, ol ol.pozitiv').each(
	    function(elem, index) {
//		Effect.BlindUp($(elem));
		elem.hide();
	    }
	);
    }

    blindUp = function(elem) {
	//$$('ol ol.negativ, ol ol.semleges, ol ol.pozitiv').each(
	$(elem).up('li').select('ol').each(
	    function(elem, index) {
//alert('ITT');
		if ($(elem).visible()) Effect.BlindUp($(elem), { duration: 0.5 });
//		elem.hide();
	    }
	);
    }

    function sleep(milliseconds) {
	var start = new Date().getTime();
	while ((new Date().getTime() - start) < milliseconds) {}
    }

    function myPSFLoader(evt) {
	hideOlOl();

	Event.observe(document.body, 'click', function(event) {
    	    var element = $(Event.element(event));

    	    if (element.hasClassName('gomb')) {

		blindUp(element);
		if (element.hasClassName('negativ')) classNev='negativ';
		else if (element.hasClassName('semleges')) classNev='semleges';
		else if (element.hasClassName('pozitiv')) classNev='pozitiv';
		$A(element.up('li').select('ol')).each(
		    function(elem, index) {
			if ($(elem).hasClassName(classNev)) {
			    if (!$(elem).visible()) {
				Effect.BlindDown($(elem), { duration: 0.5 });
				$(elem).down('input').checked=true;
			    }
			}
			//$(elem).show();

		    }
		);
	    }
	})

    }
