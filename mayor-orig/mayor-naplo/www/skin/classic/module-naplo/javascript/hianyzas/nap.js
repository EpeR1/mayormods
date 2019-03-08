
Event.observe(window, 'load', myPSFLoader, false);

function myPSFLoader(evt) {
    $$('table input[type=text].perc').each(
        function (elem, index) {
            Event.observe(elem, 'change', function(event) {
                var element = $(Event.element(event));
		element.previous('input').checked=(element.value != '0');
            }); // Event.observe
        }
    );
}
