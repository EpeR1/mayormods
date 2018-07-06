/*
Event.observe(window, 'load', myPSFLoader, false);

function myPSFLoader(evt) {

    doOnChange = function(event) {
	var element = $(Event.element(event));
        if (element.hasClassName('orarendTankor')) {
	    var idArray = element.getAttribute('id').split('_');
	    var id = idArray[1]+'_'+idArray[2]+'_'+idArray[3];
	    var orarendTankor = element.up('table').getElementsBySelector('select.orarendTankor');
	    var ertek = $F(element);
	    $A(orarendTankor).each(
                function (elem, index) {
		    if (elem.getAttribute('id').include(id)) {
			elem.setValue(ertek);
		    }
                }
            );  

        }
    }

    Event.observe(document.body, 'mayor:change',  doOnChange);

    Event.observe(document.body, 'change', doOnChange);

//    Sortable.create('test', { tag:'div', overlap:'horizontal',constraint:false });
    Sortable.create('orarend', { tag:'div', only:'draggable' , 
	tree: true, 
	treeTag: 'div',
	overlap:'vertical',
	dropOnEmpty:true,
	constraint:false,
	onEnd: function(){
    	    alert('onEnd');
	},
	onChange:function(e){
//	    $('infoBox').update( Form.Element.getValue( e.down('input')) );

	    e.addClassName('moved');

	    e.down('input').checked=false;
	    e.down('input').hide();

//	    e.down('input').value='test';

	    // ez az eredeti helye (het.nap.ora.tanarId)
	    $('infoBox1').update( (e.down('input').value) );
	    // ez az új helye
	    $('infoBox2').update( e.up('td').getAttribute('id') );

	} });

//    new Draggable('draggable', { revert: true });
//    Droppables.add('droppable', { accept: 'draggable' });

//    Sortable.create(
//        'sortable',
//        {onUpdate:function(){$('debug').update(++callsToOnUpdate+' call(s) to onUpdate')}}
//    );

//    $('targySelect').disable();
//    $('targySelect').hide(); $('mozgat').hide();
//    $('action').value='ujSorrend';
//    $('targyUl').show(); $('submit').show();


//    Sortable.create(
//	"draggable",
//	{dropOnEmpty:true,handle:'handle',containment:["draggable"],constraint:false,onChange:function(){}}
//    );


//    $('serialize').observe('click', function(event) {
//        alert(Sortable.serialize('sortable'));
//    });

}
*/