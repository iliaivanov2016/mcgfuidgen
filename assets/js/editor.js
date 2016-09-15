function mcgfuidgen_generate_id(len,t,sep,freq,seq_start,seq_step){
	var separator = "",abc="",abc_len,x, i, c,s="";
	if (seq_start >= 0) {
		abc = "" +  (seq_start + seq_step);
	} else {
		if (t == "digits")
		 abc = MCGFUIDGEN_DIGITS;
		else
		if (t == "upper")
		 abc = MCGFUIDGEN_ABC.toUpperCase();
		else
		if (t == "lower")
		 abc = MCGFUIDGEN_ABC.toLowerCase();
		else
		if (t == "mixed")
		 abc = MCGFUIDGEN_ABC.toUpperCase()+MCGFUIDGEN_ABC.toLowerCase();
		else
		if (t == "upper_digits")
		 abc = MCGFUIDGEN_DIGITS+MCGFUIDGEN_ABC.toUpperCase();
		else
		if (t == "lower_digits")
		 abc = MCGFUIDGEN_DIGITS+MCGFUIDGEN_ABC.toLowerCase();
		else
		if (t == "mixed_digits")
		 abc = MCGFUIDGEN_DIGITS+MCGFUIDGEN_ABC.toUpperCase()+MCGFUIDGEN_ABC.toLowerCase();
		else
		 abc = MCGFUIDGEN_DIGITS+MCGFUIDGEN_ABC.toUpperCase()+MCGFUIDGEN_ABC.toLowerCase()+MCGFUIDGEN_SYMBOLS;
	}
	if (sep == "space") separator = " ";
	else
	if (sep == "dash") separator = "-";
	else
	if (sep == "comma") separator = ",";
	else
	if (sep == "dot") separator = ".";
	else
	if (sep == "quote") separator = "&#39;";
	else
	if (sep == "quote2") separator = "&#34;";
	else
	if (sep == "underscore") separator = "_";
	abc_len = abc.length;
	var dt = new Date();
//console.log("rand len = "+len+" separator = "+separator+" freq = "+freq);
//console.log(abc);
	for (i = 1; i <= len; i++ ) {
		if (seq_start >= 0) {
			c = abc.substr(i-1,1);
		} else {
			x = Math.round(Math.random() * 1000000 + dt.getMilliseconds() ) % abc_len;
//console.log("i = "+i+" x = "+x);
			c = abc.substr(x,1);
		}
//console.log("c = "+c);
		s += c;
		if (freq > 0)
			if ((((len-i) % freq) == 0) && (i < len))
			 s += separator;
	}
//console.log("res = "+s);
	return s;
}

function mcgfuidgen_render_preview(){
	var len = parseInt(jQuery("#field_uidgen_length").val());
	if (isNaN(len) || (len < 1) || (len > 100)) len = 6;
	var pr_type = jQuery("#field_uidgen_render").val();
	var s = "";
	if (pr_type == "html")  {
		s += jQuery("#field_uidgen_render_custom_html").val();
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
	var t = jQuery("#field_uidgen_chartype").val();
	var sep = jQuery("#field_uidgen_separator").val();
	var freq = jQuery("#field_uidgen_separatorfreq").val();
	var seq_start = -1;
	var seq_step = 1;
	if (jQuery("#field_sequence_enabled").is(":checked")) {
		seq_start = parseInt(jQuery("#field_sequence_start").val());
		seq_step = parseInt(jQuery("#field_sequence_step").val());
	}
	var val = mcgfuidgen_generate_id(len,t,sep,freq,seq_start,seq_step);
//console.log("val = "+val);
	s = s.replace(MCGFUIDGEN_UNQIUEID_TAG,val);
	jQuery("#mcgfuidgen_preview").remove();
	jQuery(".gfield_description").after('<div id="mcgfuidgen_preview"></div>');
	jQuery("#mcgfuidgen_preview").html(s);
}

function mcgfuidgen_save_settings(){
	var len = parseInt(jQuery("#field_uidgen_length").val());
	if (isNaN(len) || (len < 1) || (len > 100)) len = 6;
	jQuery("#field_uidgen_length").val(len);
	var ct = "" + jQuery("#field_uidgen_chartype").val();
	if ((ct == "digits") && (len <= 10)) {
		jQuery("#field_sequence_enabled").prop("disabled",false);
		jQuery("#field_sequence_start").prop("disabled",false);
		var v = ""+jQuery("#field_sequence_start").val();
		if (v.length != len) {
			var x = Math.pow(10,(len-1));
			if (x <= 1)
			 x = 0;
//console.log("old len = "+v.length+", len = "+len+", x = " +x);
			jQuery("#field_sequence_start").val(x);
		}
		jQuery("#field_sequence_step").prop("disabled",false);
	} else {
		jQuery("#field_sequence_enabled").prop("checked",false).prop("disabled",true);
		jQuery("#field_sequence_start").prop("disabled",true);
		jQuery("#field_sequence_step").prop("disabled",true);
	}
	if (jQuery("#field_sequence_enabled").is(":checked")) {
		jQuery("#field_uidgen_chartype").val("digits");
		var step = parseInt(jQuery("#field_sequence_step").val());
		if (isNaN(step) || (step < 1)) step = 1;
		jQuery("#field_sequence_step").val(step);
		var start = parseInt(jQuery("#sequence_start").val());
		if (isNaN(start) || (start < 1)) start = 100000;
		jQuery("#field_sequence_start").val(start);
	}
	var data = {
		"max_length": 		jQuery("#field_uidgen_length").val(),
		"char_type": 		jQuery("#field_uidgen_chartype").val(),
		"separator":		jQuery("#field_uidgen_separator").val(),
		"separator_freq":	jQuery("#field_uidgen_separatorfreq").val(),
		"sequence_on":		(jQuery("#field_sequence_enabled").is(":checked"))?1:0,
		"sequence_start":	jQuery("#field_sequence_start").val(),
		"sequence_step":	jQuery("#field_sequence_step").val(),
		"render_type":		jQuery("#field_uidgen_render").val(),
		"render_html":		jQuery("#field_uidgen_render_custom_html").val()
	};
	var str = jQuery.stringify(data);
	SetFieldProperty('mcgfuidgen_settings', str);
	mcgfuidgen_render_preview();
//console.log("save");console.log(str);
}

function  mcgfuidgen_default_settings() {
//console.log("reset default settings");
	jQuery("#field_uidgen_length").val(6);
	jQuery("#field_uidgen_chartype").val("digits");
	jQuery("#field_uidgen_separator").val("space");
	jQuery("#field_uidgen_separatorfreq").val("3");
	jQuery("#field_sequence_enabled").prop("checked",true);
	jQuery("#field_sequence_start").val("100001");
	jQuery("#field_sequence_step").val("1");
	jQuery("#field_uidgen_render").val("text");
	jQuery("#field_uidgen_render_custom_html").val("<div>"+MCGFUIDGEN_UNQIUEID_TAG+"</div>");
	mcgfuidgen_save_settings();
}

function  mcgfuidgen_load_settings(field){
//console.log("load");console.log(field);
	try {
		var data = jQuery.parseJSON(field.mcgfuidgen_settings);
		jQuery("#field_uidgen_length").val(data.max_length);
		jQuery("#field_uidgen_chartype").val(data.char_type);
		jQuery("#field_uidgen_separator").val(data.separator);
		jQuery("#field_uidgen_separatorfreq").val(data.separator_freq);
		jQuery("#field_sequence_enabled").prop("checked",(parseInt(data.sequence_on) > 0));
		jQuery("#field_sequence_start").val(data.sequence_start);
		jQuery("#field_sequence_step").val(data.sequence_step);
		jQuery("#field_uidgen_render").val(data.render_type);
		jQuery("#field_uidgen_render_custom_html").val(data.render_html);
	} catch(e) {
		mcgfuidgen_default_settings();
	}
	mcgfuidgen_render_preview();
}

fieldSettings["text"] += ", .field_uidgen";

//binding to the load field settings event to initialize the checkbox
jQuery(document).bind("gform_load_field_settings", function(event, field, form){
//console.log(field);
	if (field.type == "uidgen") {
        // unhide all udgen settings
		jQuery(".field_uidgen").show();
        // unhide common settings used in uidgen
		jQuery(".label_setting").show();
		jQuery(".description_setting").show();
        jQuery("#gform_tab_2").parent().show();
		jQuery(".admin_label_setting").show();
		jQuery(".visibility_setting").show();
		jQuery(".post_custom_field_setting").show();
		jQuery(".prepopulate_field_setting").show();
		jQuery(".conditional_logic_field_setting").show();
        jQuery("#gform_tab_3").parent().show();
		jQuery(".placeholder_setting").show();
		jQuery(".placeholder_textarea_setting").show();
		jQuery(".field_description_placement_container").show();
		jQuery(".css_class_setting").show();
		jQuery(".size_setting").show();
		// hide not needed fields
		jQuery(".placeholder_setting").hide();
		jQuery(".placeholder_textarea_setting").hide();
		jQuery(".error_message_setting").hide();
		mcgfuidgen_load_settings(field);
	} else {
		jQuery(".field_uidgen").hide();
	}
//			SetFieldDescription("my description");
});

jQuery(document).bind("gform_field_added",function(event, form, field){
	//console.log(event);console.log(field);console.log(form);console.log("added");
	if (field.type == "uidgen") {
    	 jQuery("#field_"+field.id+" .gfield_label").html("Unique ID");
    }
	//console.log("fid = "+field.id);
});