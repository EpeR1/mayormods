includeJS('skin/classic/share/javascript/TableFilter/tablefilter_all_min.js');
includeJS('skin/classic/share/javascript/TableFilter/sortabletable.js');
includeJS('skin/classic/share/javascript/TableFilter/tfAdapter.sortabletable.js');
Event.observe(window, 'load', myPSFLoader, false);

function myPSFLoader(evt) {

    kivalaszt = function(event) {
	$$('#kivalasztott option').each(
	    function(elem, index) {
		elem.selected=true;
	    }
	);
    }
    mezoKivalasztas = function(event) {
	optionAthelyezes('szabad','kivalasztott');
    }
    mezoTorles = function(event) {
	optionAthelyezes('kivalasztott','szabad');
    }
    optionAthelyezes = function(fromId, toId) {
	var fromSel = $(fromId);
	var toSel = $(toId);
	// Áthelyezés
	for (i = 0; i < fromSel.length; i++) {
	    if (fromSel.options[i].selected) {
		var val = fromSel.options[i].value;
		var txt = fromSel.options[i].innerHTML;
		if (toSel.selectedIndex == -1) {
		    toSel.insert({bottom: new Element('option', {value: val}).update(txt)});
		} else {
		    toSel.options[ toSel.selectedIndex ].insert({before: new Element('option', {value: val}).update(txt)});
		}
	    }
	}
	// Törlés
	for (i = fromSel.length-1; i >= 0; i--) {
	    if (fromSel.options[i].selected) {
		fromSel.options[i].remove();
	    }
	}
    }

    
    // Mezőkiválasztó gombok kezelése
    var FieldSelectObject = Class.create();
    FieldSelectObject.prototype = {
        initialize: function(element) {
            this.element = $(element);
            this.element.observe('click',this.fieldSelect.bindAsEventListener(this));
        },

        fieldSelect: function(evt, extraInfo) {

	    // Az összes elem kiválasztása...
	    kivalaszt();
	    // ... és átrakása a szabad mezők közé
	    optionAthelyezes('kivalasztott','szabad');
	    // A kiválasztandó elemek kijelölése
	    this.list = $F($(this.element.getAttribute('name')+'Lista'));
	    this.idList = this.list.split(',');
	    this.options = $('szabad').options;
	    // és áthelyezése egyesével (a megfelelő sorrend miatt!!)
	    for (j=0; j<this.idList.length; j++) {
		for (i=0; i<this.options.length; i++) {
		    attr = this.options[i].value;
		    this.options[i].selected = (attr == this.idList[j]);
		}
		optionAthelyezes('szabad','kivalasztott');
	    }

            Event.stop(evt);
        }
    }

    var onClickMezoSelectElements = new Array();
    $$('.onClickMezoSelect').each(
        function (elem, index) {
            onClickMezoSelectElements.push(new FieldSelectObject(elem));
        }
    );

    // A szűrés
    if ($('diakTabla') != null) {
	var lastRowIndex = tf_Tag(tf_Id('diakTabla'),"tr").length; 
	var table1Filters = {
	    base_path: 'skin/classic/share/javascript/TableFilter/',
	    single_search_filter: false,
	    sort: true,
	    sort_config: {sort_types: []},
	    alternate_rows: true,
	    on_keyup : true,
	    highlight_keywords : true,
	    mark_active_columns: true,
	    paging: false,
	    rows_counter: true,
	    rows_counter_text: 'Találatok száma: ',
	    input_watermark: 'Keresés...',
	    filters_row_index: 1,
	    remember_grid_values: true,
	    rows_always_visible:  [lastRowIndex]
//sort_config: {sort_types: ['Number','Number','String','String','String']},
//sort_config: {sort_types: cellTypes},
//extensions: {     
//                        name:['ColumnsResizer'],   
//                        src:['TFExt_ColsResizer/TFExt_ColsResizer.js'],   
//                        description:['Columns Resizing'],   
//                        initialize:[function(o){o.SetColsResizer();}]  
//},  
//col_resizer_all_cells: true,
//editable: false,
//selectable: true,
//ezEditTable_config: {
//    default_selection: 'both',
//}
//        col_1: "select",
//        col_2: "select",
//        btn: true
	}
	if (typeof(selIndex) !== 'undefined') for (i = 0; i < selIndex.length; i++) eval('table1Filters.col_'+selIndex[i]+' = "select";');
	eval('table1Filters.col_'+cellTypes.length+' = "none";');
	if (typeof(cellTypes) !== 'undefined') table1Filters.sort_config.sort_types = cellTypes;
	var tf01 = setFilterGrid("diakTabla",1,table1Filters);
    } // van diakTabla

    Event.observe('valaszt', 'click', mezoKivalasztas);
    Event.observe('torol', 'click', mezoTorles);
    Event.observe('exportOptions', 'submit', kivalaszt);
}


/*
// A filter cseréje!
tf01.RemoveGrid();
var table1Filters = {
	single_search_filter: true,
	sort: true,
}
tf01 = new TF("diakTabla",1,table1Filters);
tf01.AddGrid();
*/
