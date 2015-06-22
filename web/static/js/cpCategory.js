
pageModel = {

	"attachments" : [],

	"getNewAttachmentUID" : function(){
		var uids = [];

		for(var c=0; c<this.attachments.length; c++){
			uids.push(this.attachments[c].uid);
		}

		if (!uids.length) return 1;

		var newUID = uids[uids.length-1];

		do { newUID++; } while ( uids.indexOf(newUID) != -1 );

		return newUID;
	},

	"getAttachmentsID" : function(){
		var tmp = [];
		for(var c=0; c<this.attachments.length; c++){
			tmp.push(parseInt(this.attachments[c].id));
		}
		return tmp;
	},

	"deleteAttachments" : function(uid){
		if ( typeof uid == "undefined" ) return null;

		if ( typeof uid == "object" && typeof uid.push == "function" ){

		} else if (typeof uid == "string") {
			var uid = _utils.msplit([",",";"],uid.trim());
			for(var c=0; c<uid.length; c++){
				uid[c] = parseInt(uid[c].trim());
			}
		} else if ( typeof uid == "number" ){
			var uid = [uid];
		}

		if ( !uid.length ) return true;

		var tmp = [];

		for( var c = 0; c < this.attachments.length; c++ ){
			var key = uid.indexOf(this.attachments[c].uid);
			if (key == -1) tmp.push(this.attachments[c]);
		}

		this.attachments = tmp;

		this.buildAttachments();

		return true;
	},

	"addAttachments" : function(arr){

		var this_ = this;

		if ( typeof arr == "undefined" ) return;

		if (
			typeof arr == "object"
			&& typeof arr.push == "undefined"
		){
			var arr = [arr];
		}

		for (var c=0; c<arr.length; c++) {

			if (
				typeof arr[c].path == "undefined"
				|| typeof arr[c].thumbnail == "undefined"
				|| typeof arr[c].id == "undefined"
			){
				continue;
			}

			this.attachments.push({
				"path" : arr[c].path,
				"thumbnail" : arr[c].thumbnail,
				"id" : arr[c].id,
				"uid" : this_.getNewAttachmentUID()
			});

		}

		if (this.attachments.length){
			this.attachments = [this.attachments[this.attachments.length-1]];
		}

		this.buildAttachments();

	},

	"buildAttachments" : function(){
		var html = [
			'<div class="addAttachmentButton nbutton dashed hoverblue inline-block">',
			'<div class="img"><span style="position:absolute; left:0px; top:0px; width:100%; text-align:center;">Добавить изображение</span></div>',
			'</div>'
		];

		for(var c=0; c<this.attachments.length; c++){
			var tmp = this.attachments[c];
			var tmp2 = [
				'<div class="thumbnail nthumbnail inline-block">',
				'<div  style="background-image:url(' + (location.rooturl + tmp.thumbnail) + ');"  class="img">',
				'<div uid="'+tmp.uid+'"  class="attachmentButton delete" title="Удалить"><span>D</span></div>',
				'<a uid="'+tmp.uid+'" class="attachmentButton full"  href="'+location.rooturl + tmp.path+'" title="Полный размер" target="_blank"><span>F</span></a>',
				'</div>',
				'</div>'
			];
			html.push(tmp2.join(''));
		}

		document.querySelector('.thumbnailsRow').innerHTML = html.join('');
	},

	"getCategoryName" : function(){
		var name = document.querySelector('[name="categoryName"]').value;
		return name;
	}

};


$(document).ready(function(){

	var AW = new AttachmentsWindow({
		"AttachmentsModel" : test = new AttachmentsModel({
			"uploadURL" : location.rooturl + '/attachments/upload/',
			"getURL" : location.rooturl + '/attachments/'
		}),
		"rootURL" : location.rooturl
	});

	AW.buttons.insertSelected.onclick = function(){
		if (AW.context == "productAttachments"){

			var attachments = AW.getSelected();

			attachments.map(function(arr){
				if (!arr) return;

				pageModel.addAttachments({
					"thumbnail" : arr.thumbnail,
					"path" : arr.path,
					"id" : arr.id
				});

			});

			AW.cleanSelected();
			AW.close();
		}
	};

	AW.getAttachments();

	// ------------------------------------------------------------------

	$(".thumbnailsRow").on("click", ".addAttachmentButton", function(){

		AW.context = "productAttachments";

		AW.open();

	});

	// ------------------------------------------------------------------

	$(".thumbnailsRow").on("click", ".attachmentButton.delete", function(){
		var uid = $(this).attr("uid");
		pageModel.deleteAttachments(uid);
	});

	// ------------------------------------------------------------------

	$("#categoryDeleteDialogWindow .closeButton").click(function(){
		$("#categoryDeleteDialogWindow").addClass("none");
	});

	// ------------------------------------------------------------------

	$("#categoryDeleteDialogWindow [type=submit]").click(function(){
		var value = $("#categoryDeleteDialogWindow select").val();
		$.ajax({
			"type" : "DELETE",
			"url" : location.rooturl + "/cp/category/" + data.category.id,
			"data" : {"substitute" : value},
			"complete" : function(res){
				var res_ = JSON.parse(res.responseText);
				if (!res_.err){
					alert("Удалено");
					location.replace(location.rooturl + "/cp/category/");
				} else {
					console.log(res_.err);
					alert("Ошибка. См. консоль");
				}
			}
		});
	});

	// ------------------------------------------------------------------

	$("#deleteCategoryButton").click(function(){

		if (
			typeof data.actionType == "undefined"
			|| data.actionType != "update"
		) {
			return null;
		}

		$.get(location.rooturl + "/category/" + data.category.id + "/?json=1&page=1&perpage=1", function(res){

			var res_ = JSON.parse(res);
			if (typeof res_.err == "undefined"){

				if (res_.length){
					$("#categoryDeleteDialogWindow").removeClass("none");
				} else {

					$.ajax({
						"type" : "DELETE",
						"url" : location.rooturl + "/cp/category/" + data.category.id + "/",
						"data" : {},
						"complete" : function(res){
							var res_ = JSON.parse(res.responseText);
							if (!res_.err){
								alert("Удалено");
								location.replace(location.rooturl + "/cp/category/");
							} else {
								console.log(res_.err);
								alert("Ошибка. См. консоль");
							}
						}
					});

				}

			} else {
				if ( typeof res_.err == "string" ){
					alert(res_.err);
				} else {
					alert("Ошибка. см. консоль");
					console.log(res_.err);
				}
			}

		})

	});

	// ------------------------------------------------------------------

	$("#saveCategoryButton").click(function(){

		if ( typeof data.actionType == "string" ){

			if (data.actionType == "add"){

				$.ajax({
					"type" : "PUT",
					"url" : location.rooturl + "/cp/category/",
					"data" : {
						"attachments" : pageModel.getAttachmentsID(),
						"name" : pageModel.getCategoryName()
					},
					"complete" : function(res){
						var res_ = JSON.parse(res.responseText);
						if (!res_.err){
							var id = res_.id;
							alert("Сохранено");
							location.replace(location.rooturl + "/cp/category/");
						} else {
							alert(res_.err);
						}
					}
				});

			} else if ( data.actionType == "update" ) {

				$.ajax({
					"type" : "POST",
					"url" : location.rooturl + "/cp/category/" + data.category.id + "/",
					"data" : {
						"attachments" : pageModel.getAttachmentsID(),
						"name" : pageModel.getCategoryName()
					},
					"complete" : function(res){
						var res_ = JSON.parse(res.responseText);
						if (!res_.err){
							alert("Сохранено");
							location.reload();
						} else {
							if ( typeof res_.err == "object" && typeof res_.err.push == "function" ){
								res_.err = res_.err.join(";");
							}

							alert(res_.err);
						}
					}
				});

			} // close.actionType == action

		} // close.typeof.actionType

	}); // close.click

}); // close.document.ready
