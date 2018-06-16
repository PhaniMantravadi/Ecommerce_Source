Event.observe(window, 'load', function() {

$$('.change-container-classname').each(function(el){ 
	if (el.readAttribute('onclick') == "bundle.changeSelection(this)"){
	    if(el.checked){
		var data = el.id.replace("bundle-option-", "");
		data = data.split("-");

		if(in_array(data[1],bundlebkId)){
		    $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+cartoptionId[data[1]]+'</span></span>';
		    $$('#product_addtocart_form .availability')[0].innerHTML=stockoptionId[data[1]];
		}
	    }
  
	    Event.observe(el, 'click',  changeBackorder);
	}	
    });
	
    $$('.change-container-classname').each(function(el){  
	if (!(el.type == 'radio' || el.type == 'checkbox')){
	    Event.observe(el, 'change',  changeBackorder);
	}
    });
});

function changeBackorder(element)
{ 
    var selectedID = new Array();
    $$('.change-container-classname').each(function(ml){ 
	if(ml.checked){
	  var rdata = ml.id.replace("bundle-option-", "");
	      rdata = rdata.split("-");	
	      selectedID.push(rdata[1]);
	}
      if (!(ml.type == 'radio' || ml.type == 'checkbox')){

	    if(ml.options[ml.selectedIndex].value != ''){
		selectedID.push(ml.options[ml.selectedIndex].value);
	    }
      }
    });
    
    var countPresentId=intersect(selectedID, bundlebkId);

    if(this.type == 'radio'){

	var radioSelected = this.id.replace("bundle-option-", "");
	radioSelected = radioSelected.split("-");
	if(countPresentId.length == 0){
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+cartoptionId[radioSelected[1]]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=stockoptionId[radioSelected[1]];
	}
	if(countPresentId.length > 0){
	       $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+defaultstockcart[0]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=defaultstockcart[1];
	}
    }
    if(this.type == 'checkbox'){
	var checkboxSelected = this.id.replace("bundle-option-", "");
	checkboxSelected = checkboxSelected.split("-");

	if(countPresentId.length == 0 ){ 
	      if(checkboxSelected[1]!==undefined){
		$$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>Add to Cart</span></span>';
		$$('#product_addtocart_form .availability')[0].innerHTML='In Stock';
	      }else{
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+cartoptionId[checkboxSelected[1]]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=stockoptionId[checkboxSelected[1]];}
	}
	if(countPresentId.length > 0 ){
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+defaultstockcart[0]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=defaultstockcart[1];
	}

	
    }
    if(this.type == 'select-multiple'){
	if(countPresentId.length == 0 ){
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+cartoptionId[this.value]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=stockoptionId[this.value];
	}
	if(countPresentId.length > 0 ){
	       $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+defaultstockcart[0]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=defaultstockcart[1];
	}
    }
    if(this.type == 'select-one') {

	  if(countPresentId.length == 0){
	      if(this.value !== undefined){
		 $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>Add to Cart</span></span>';
		$$('#product_addtocart_form .availability')[0].innerHTML='In Stock';
	      }else{
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+cartoptionId[this.value]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=stockoptionId[this.value];
	      }
	  }
	  if(countPresentId.length > 0){
	     
	      $$('#product_addtocart_form .btn-cart')[0].innerHTML='<span><span>'+defaultstockcart[0]+'</span></span>';
	      $$('#product_addtocart_form .availability')[0].innerHTML=defaultstockcart[1];
	  }
    }
}


function intersect(a, b) {
    var t;
    if (b.length > a.length) t = b, b = a, a = t; // indexOf to loop over shorter
    return a.filter(function (e) {
        if (b.indexOf(e) !== -1) return true;
    });
}
function in_array(value, array) {
    for(var i = 0; i < array.length; i++) 
    {
        if(array[i] == value) return true;
    }
    return false;
} 
