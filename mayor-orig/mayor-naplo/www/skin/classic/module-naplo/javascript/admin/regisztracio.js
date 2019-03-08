
    Event.observe(window, 'load', myPSFLoader, false);

    function myPSFLoader(evt) {
	// A regisztrációs form elküldése
	$('regForm').submit();
    }
