$(document).ready(function(){
	$(".faq_content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!", event:"dblclick" });
	$(".content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!", event:"dblclick" });
	$(".main_body_text").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!", event: "dblclick" });
	$(".contact_content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!", event: "dblclick" });
	$(".rules_content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!", event:"dblclick" });
		
	});