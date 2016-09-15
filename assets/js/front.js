function mcgfuidgen_createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else var expires = "";
    document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
}

function mcgfuidgen_readCookie(name) {
    var nameEQ = escape(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length, c.length));
    }
    return "";
}

function mcgfuidgen_eraseCookie(name) {
    mcgfuidgen_createCookie(name, "", -1);
}

function mcgfuidgen_render_uidgen(id,settings,value){
	var pr_type = settings.field_uidgen_render;
	var s = "";
	if (pr_type == "html")  {
		s += settings.render_html;
		if (s.indexOf(MCGFUIDGEN_UNQIUEID_TAG) < 0) s += MCGFUIDGEN_UNQIUEID_TAG;
	} else
	if (pr_type == "span")  {
		s = '<span>'+MCGFUIDGEN_UNQIUEID_TAG+'</span>';
	} else
	if (pr_type == "div")  {
		s = '<div>'+MCGFUIDGEN_UNQIUEID_TAG+'</div>';
	} else
	if (pr_type == "hidden")  {
		s = '<input type="hidden" value="'+MCGFUIDGEN_UNQIUEID_TAG+'"/>';
	} else {
		s = '<input type="text" value="'+MCGFUIDGEN_UNQIUEID_TAG+'" readonly="readonly"/>';
	}
	s = s.replace(MCGFUIDGEN_UNQIUEID_TAG,value);
	jQuery("#"+id).find("mcgfuidgen_html").html(s);
}

function mcgfuidgen_do_init(){
console.log(">mcgfuidgen_do_init form_id = "+MCGFUIDGEN_FORM_ID);
	/*
	jQuery(".gform_uidgen").each(function(){
		var id = "" + jQuery(this).attr("id");
console.log("id = "+id);
		var field_id = id.replace('field_'+MCGFUIDGEN_FORM_ID+'_','');
console.log("field_id = "+field_id);
		jQuery.post(MCGFUIDGEN_AJAX_URL,{
			"action": "mcgfuidgen_get_uidgen_value",
			"form_id":  "" + jQuery(this).find(".mcgfuidgen_form_id").val(),
			"entry_id": "" + jQuery(this).find(".mcgfuidgen_entry_id").val(),
			"field_id": field_id
		},function(s){
			try {
console.log(s);
				var res = jQuery.parseJSON(s);
				mcgfuidgen_render_uidgen(id,res.settings,res.value)
console.log(res);console.log(settings);
				jQuery("#input_"+MCGFUIDGEN_FORM_ID+"_"+field_id).val(res.value);
			} catch(e) {}
		});
	});
	*/
}

function mcgfuidgen_init(){
console.log(">mcgfuidgen_init");
	if (!window.jQuery) {
		setTimeout("mcgfuidgen_init()",100);
		return;
	}
  jQuery(document).bind('gform_post_render', function(){
	  mcgfuidgen_do_init();
  });
}