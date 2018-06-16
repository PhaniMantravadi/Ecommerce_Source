//ECFPlus New Version scripts
jQuery(document).ready(function(){
	jQuery('.ecButton-CR').click(function(){
		jQuery('.ecfPlus-CR').toggleClass('formOpen-CR');
		jQuery('.ecForm-CR').toggleClass('formOpen-CR');
		jQuery('.ecButton-CR').toggleClass('mobButton-CR');
	});
	jQuery('.ecButton-CL').click(function(){
		jQuery('.ecfPlus-CL').toggleClass('formOpen-CL');
		jQuery('.ecForm-CL').toggleClass('formOpen-CL');
		jQuery('.ecButton-CL').toggleClass('mobButton-CL');
	});
}); 