AttachmentsWindow = function(arg){
	this.AttachmentsModel = null;
	this.content = {};
	this.context = null;
	this.init(arg);
};

AttachmentsWindow.prototype = {

	"instances" : [],

	"init" : function(arg){
		if ( typeof arg == "undefined" ) var arg = Object.create(null);

		if (typeof arg.AttachmentsModel == "undefined"){
			// return null;
		}

		this.rootURL = arg.rootURL;

		this.AttachmentsModel = arg.AttachmentsModel;

		this.buildStyles();

		this.buildMarkup();

		this.addEvents();

		this.instances.push(this);

		this.close();
	},

	"buildStyles" : function(){
		var css = [
			".attachmentWindow.none {display:none;}",
			".attachmentWindow {position:absolute; left:0px; top:0px; margin:32px; width:800px; padding:24px; background-color:white; border:1px solid #999; box-shadow:8px 8px 0px rgba(0, 0, 0, 0.35);}",
			".attachmentWindow .title { font-size:16px; text-align:center;}",
			".attachmentWindow .vd {position:relative; border-bottom:1px solid #c8c8c8; margin:12px 0;}",
			".attachmentWindow .ed {height:10px;}",
			".attachmentWindow .content {padding:12px 0; border:1px solid #c8c8c8; min-height:256px; max-hight:640px; overflow-y:auto; overflow-x:hidden;}",
			".attachmentWindow .content .attachment {display:inline-block; position:relative; margin:5px; padding:5px; background-color:white; border:1px solid #c8c8c8;}",
			".attachmentWindow .content .attachment:hover {border:1px solid #666;}",
			".attachmentWindow .content .img {height:64px; width:64px; position:relative; display:block; background-color:whitesmoke; background-size:cover; background-position:center;}",
			".attachmentWindow .content .attachment [type='checkbox'] {position:absolute; height:18px; width:18px; top:-9px; right:-9px; margin: 5px;}",
			".attachmentWindow .attachmentButton {display:none; position:absolute; height:18px; width:18px; margin: 5px; background-color:black; border-radius:3px; border:1px solid white;}",
			".attachmentWindow .attachmentButton span {display:table-cell; color:white; vertical-align:middle; text-align:center;}",
			".attachmentWindow .attachment:hover .attachmentButton {display:table;}",
			".attachmentWindow .attachmentButton:hover {background-color:#666;}",
			".attachmentWindow .attachmentButton.fullsize {bottom:-9px; right:-9px;}",
			".attachmentWindow .page {text-align:left;}",
			".attachmentWindow .page input {margin-right:12px;}",
			".attachmentWindow .closeButton {position:absolute; top:0px; right:0px; color:#b80000; font-size:12px; font-weight:bold; margin:10px; cursor:pointer;}",
			".attachmentWindow .closeButton:hover {text-decoration:underline;}",
			".attachmentWindow .upload {padding:10px; background-color:whitesmoke; border:1px solid #c8c8c8}"
		];

		var head = document.querySelector("head");

		var styleElem = head.querySelector("#attachmentWindowStyle");

		if ( !styleElem ){
			var styleElem = document.createElement("style");
			styleElem.id = "attachmentWindowStyle";
			head.appendChild(styleElem);
		}

		styleElem.innerHTML = css.join(' ');
	},

	"buildMarkup" : function(){

		var url = this.AttachmentsModel.uploadURL;

		var html = [
			'<div class="title">Медиафайлы</div>',
			'<div class="vd"></div>',
			'<div class="content"></div>',
			'<div class="vd"></div>',
			'<div class="page">',
			// '<select></select>',
			'<input style="width:auto;" class="insert" type="submit" value="Вставить выбранное">',
			'</div>',
			'<div class="ed"></div>',
			'<div class="upload">',
			'Загрузить новое изображение: ',
			'<input type="file" name="files[]" multiple="multiple" >',
			'<input type="submit" />',
			'</div>',
			'<div class="closeButton">[x]Закрыть</div>'
		]

		var body = document.querySelector("body");

		var elem = body.querySelector(".attachmentWindow");

		if (!elem) {
			var elem = document.createElement("div");
			elem.className = "attachmentWindow";
			elem.innerHTML = html.join('');
			body.appendChild(elem);
		}

		this.elem = elem;

		this.buttons = Object.create(null);

		this.buttons.insertSelected = this.elem.querySelector(".insert");
	},

	"addEvents" : function(){
		var this_ = this;

		var closeB = this.elem.querySelector(".closeButton");
		if ( closeB ){
			closeB.addEventListener("click", function(){ this_.close(); }, null);
		}

		var uploadSubmitB = this.elem.querySelector(".upload [type='submit']");
		if( uploadSubmitB ){
			uploadSubmitB.addEventListener("click", function(){ this_.uploadAttachments(); }, null);
		}

	},

	"getSelected" : function(){
		var checked = this.elem.querySelectorAll(".content :checked");
		var tmp = [];
		for(var c=0; c<checked.length; c++){
			var id = checked[c].getAttribute("data-id");
			tmp.push(this.content[id]);
		}
		return tmp;
	},

	"cleanSelected" : function(){
		var checked = this.elem.querySelectorAll(".content :checked");
		for(var c=0; c<checked.length; c++){
			checked[c].checked = false;
		}
	},

	"getAttachments" : function(){
		var contentElem = this.elem.querySelector(".content");

		var this_ = this;

		if (!this.AttachmentsModel) return null;

		this.AttachmentsModel.getAttachments({
			"callback":function(res){
				var html = '';
				if (!res) return null;
				this_.content = Object.create(null);
				for(var c=0; c<res.length; c++){
					this_.content[res[c].id] = res[c];
					var part = [
						'<div class="attachment">',
						'<div class="img" style="background-image:url('+this_.rootURL+res[c].thumbnail+')"></div>',
						'<input data-id="'+res[c].id+'" type="checkbox">',
						'<a target="_blank" title="Полный размер" href="'+this_.rootURL+res[c].path+'" class="attachmentButton fullsize"><span>F</span></a>',
						'</div>'
					];
					html += part.join('');
				}

				contentElem.innerHTML = html;
			}
		});

	},

	"uploadAttachments" : function(){
		var this_ = this;
		var input = this_.elem.querySelector(".upload [type='file']");
		if (input.files.length){
			this.AttachmentsModel.upload({
				"files" : input.files,
				"callback" : function(res){
					if ( res !== null ){
						if (typeof res.errors != "undefined"){
							if ( res.errors === null ){

							} else if (typeof res.errors == "string" && res.errors) {
								alert(res.errors);
							} else if (typeof res.errors == "object" && typeof res.errors.join == "function") {
								if ( res.errors.length ) {
									alert(res.errors.join("; "));
								}
							} else {
								alert("Ошибка. См. консоль");
								console.log(res.errors);
							}
						}
					}
					this_.getAttachments();
				}
			});
		}
	},

	"open" : function(){
		this.elem.classList.remove("none");
	},

	"close" : function(){
		var this_ = this;
		this_.elem.classList.add("none");
	}

};



// ----------------------------------------------------------------------------------------------------------------------------



AttachmentsModel = function(arg){
	this.init(arg);
}

AttachmentsModel.prototype = {
	"init" : function(arg){
		if(typeof arg == "undefined") var arg = Object.create(null);
		if (
			typeof arg.uploadURL == "undefined"
			|| typeof arg.getURL == "undefined"
		){
			return null;
		}

		this.uploadURL = arg.uploadURL;
		this.getURL = arg.getURL;
	},

	"upload" : function(arg){

		if (typeof arg == "undefined") return null;

		if (typeof arg.files == "undefined") return null;

		var callback = ( typeof arg.callback == "function" ? arg.callback : function(){} );

		var files = arg.files;

		var formdata = new FormData();

		for(var c=0; c<files.length; c++){
			formdata.append("files[]", files[c]);
		}

		var http = new XMLHttpRequest;

		http.onreadystatechange=function(){
			if (http.readyState==4 && http.status==200){
				callback(JSON.parse(http.responseText));
			}
		};

		http.ontimeout = function(){ callback(http);};

		http.onerror = function(){ callback(http); };

		http.open("POST", this.uploadURL, true);

		http.send(formdata);

	},

	"getAttachments" : function(arg){

		if (typeof arg == "undefined") return null;

		var query = [];

		if (typeof arg.id == "object"){
			query.push('id='+arg.id.join(','));
		}

		var callback = (typeof arg.callback == "function" ? arg.callback : function(){} );

		var http = new XMLHttpRequest;

		http.onreadystatechange=function(){
			if (http.readyState==4 && http.status==200){
				callback(JSON.parse(http.responseText));
			}
		};

		http.ontimeout = function(){ callback(http);};

		http.onerror = function(){ callback(http); };

		http.open(
			"GET",
			this.getURL + (query.length ? '?' + query.join('&') : ''),
			true
		);

		http.send();

	}
};