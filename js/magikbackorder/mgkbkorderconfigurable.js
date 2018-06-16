Event.observe(window, 'load', function() {
    $$('.super-attribute-select').each(function(el){
        Event.observe(el, 'change',  applyBackorder);
    });
});

function applyBackorder() {
    this.settings   = $$('.super-attribute-select');
    var count = this.settings.length;
    var simpleProductId = spConfig.selectedProductId();
    if(simpleProductId){
	    $$('#product_addtocart_form .btn-cart')[0].innerHTML = '<span><span>'+backorderDetail[simpleProductId]+'</span></span>';
	    $$('#product_addtocart_form .availability')[0].innerHTML=bkorderAvailable[simpleProductId];
	    if(!$('selected_options_div')){
		$$('.short-description')[0].insert({ top : "<div id='selected_options_div'></div>"});
	    }   
	    $('selected_options_div').update(bkorderGnote[simpleProductId]);
    }else{ 
	    $$('#product_addtocart_form .btn-cart')[0].innerHTML = '<span><span>Add to Cart</span></span>';
	    $$('#product_addtocart_form .availability')[0].innerHTML='<span> In Stock</span>';
	    if($('selected_options_div')){
		$('selected_options_div').update();
	    }
    }
}

Product.Config.prototype.getSelectedOptionsProducts = function () {
    var existingProducts = {};

    for(var i=this.settings.length-1;i>=0;i--)
    {
        var selected = this.settings[i].options[this.settings[i].selectedIndex];
        if (selected && selected.config)
        {
            for(var productsopt=0;productsopt<selected.config.products.length;productsopt++)
            {
                var finalKey = selected.config.products[productsopt]+"";
                if(existingProducts[finalKey]==undefined)
                {
                    existingProducts[finalKey]=1;
                }
                else
                {
                    existingProducts[finalKey]=existingProducts[finalKey]+1;
                }
            }
        }
    }

    return existingProducts;
};

Product.Config.prototype.selectedProductId = function()
{
    var existingProducts = this.getSelectedOptionsProducts();

    for (var keyValue in existingProducts)
    {
        for ( var keyValueInner in existingProducts)
        {
            if(Number(existingProducts[keyValueInner])<Number(existingProducts[keyValue]))
            {
                delete existingProducts[keyValueInner];
            }
        }
    }

    var allowProductssize = 0;
    var livesimpleproductId;
    for ( var keyValue in existingProducts)
    {
        livesimpleproductId = keyValue;
        allowProductssize++;
        if (allowProductssize > 1) {
            break;
        }
    }

    return allowProductssize == 1 ? livesimpleproductId : null;
};

            
            
          

