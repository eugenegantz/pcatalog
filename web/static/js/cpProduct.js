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

		this.buildAttachments();

	},

	"buildAttachments" : function(){
		var html = [
			'<div class="addAttachmentButton nbutton dashed hoverblue inline-block">',
			'<div class="img"><span style="position:absolute; width:100%; text-align:center; left:0; top:0;">Добавить изображение</span></div>',
			'</div>'
		];

		for(var c=0; c<this.attachments.length; c++){
			var tmp = this.attachments[c];
			var tmp2 = [
				'<div class="nthumbnail inline-block">',
				'<div  style="background-image:url(' + (location.rooturl + tmp.thumbnail) + ');"  class="img">',
				'<div uid="'+tmp.uid+'"  class="attachmentButton delete" title="Удалить"><span>D</span></div>',
				'<a uid="'+tmp.uid+'" class="attachmentButton full"  href="'+location.rooturl + tmp.path+'" title="Полный размер" target="_blank"><span>F</span></a>',
				'</div>',
				'</div>'
			];
			html.push(tmp2.join(''));
		}

		document.querySelector('.thumbnailsRow').innerHTML = html.join('');
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

	$("#deleteProductButton").click(function(){
		$.ajax({
			"url" : location.rooturl + "/cp/product/" + window.data.product.id + "/",
			"type" : "DELETE",
			"data" : {},
			"complete" : function(res){
				var res = JSON.parse(res.responseText);
				if ( res.err ) {
					if ( typeof res.err == "object" && typeof res.err.push == "function" ){
						alert("Ошибка: " + res.err.join("; "));
					} else if ( typeof res.err == "string" ) {
						alert("Ошибка: " + res.err);
					} else {
						alert("Ошибка. см. консоль");
						console.log(res.err);
					}
				} else {
					alert("Удалено");
					location.replace(location.rooturl + "/cp/product/");
				}
			},
			"error" : function(){
				alert("Ошибка соединения с сервером!");
			}
		})
	});

	// ------------------------------------------------------------------

	$("#saveProductButton").click(function(){

		var data = Object.create(null);

		data.name = {"value" : $("[name=name]").val(), "name" : "Имя"};
		data.price = {"value" : $("[name=price]").val(), "name" : "Цена"};
		data.category = {"value" : $("[name=category]").attr("msdb_value"), "name" : "Категория"};
		data.amount = {"value" : $("[name=amount]").val(), "name" : "Количество"};
		data.measure = {"value" : $("[name=measure]").val(), "name" : "Ед. измерения"};
		data.description = {"value" : $("textarea[name=description]").val(), "name" : "Описание"};
		data.status = {"value" : $("[name=status]").val(), "name" : "Статус"};

		for(var prop in data){
			if (!data[prop].value){
				alert("Заполнены не все поля: " + prop);
				return;
			} else {
				data[prop] = data[prop].value;
			}
		}

		data.attachments = pageModel.getAttachmentsID();

		if ( typeof window.data.actionType == "string" ) {

			if ( window.data.actionType == "update" ) {

				$.ajax({
					"url" : location.rooturl + "/cp/product/" + window.data.product.id + "/",
					"type" : "POST",
					"data" : data,
					"complete" : function(res){
						var res = JSON.parse(res.responseText);
						if ( res.err ) {
							if ( typeof res.err == "object" && typeof res.err.push == "function" ){
								alert("Ошибка: " + res.err.join("; "));
							} else if ( typeof res.err == "string" ) {
								alert("Ошибка: " + res.err);
							} else {
								alert("Ошибка. см. консоль");
								console.log(res.err);
							}
						} else {
							alert("Сохранено");
							location.reload();
						}
					},
					"error" : function(){
						alert("Ошибка соединения с сервером!");
					}
				})

			} else if ( window.data.actionType == "add" ) {

				$.ajax({
					"url" : location.rooturl + "/cp/product/",
					"type" : "PUT",
					"data" : data,
					"complete" : function(res){
						var res = JSON.parse(res.responseText);
						if ( res.err ) {
							if ( typeof res.err == "object" && typeof res.err.push == "function" ){
								alert("Ошибка: " + res.err.join("; "));
							} else if ( typeof res.err == "string" ) {
								alert("Ошибка: " + res.err);
							} else {
								alert("Ошибка. см. консоль");
								console.log(res.err);
							}
						} else {
							alert("Сохранено")
							location.replace(location.rooturl + "/cp/product/");
						}
					},
					"error" : function(){
						alert("Ошибка соединения с сервером!");
					}
				})

			}

		} // close.actionType

	}); // close.click

}); // close.document.ready