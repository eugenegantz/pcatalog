_utils = Object.create(null);

_utils.msplit = function(d,s){
	var regx = new RegExp('[' + d.join('') + ']','g');
	s = s.replace(regx,d[0]);
	return s.split(d[0]);
}

_utils.arrayRemove = function(arr, from, to) {
	var rest = arr.slice((to || from) + 1 || arr.length);
	arr.length = from < 0 ? arr.length + from : from;
	return arr.push.apply(arr, rest);
};

_utils.URLHashParse = function (){
	var vars = {};
	if ( !!location.hash ){
		var hash = location.hash.replace('#','');
		var hash = hash.split('&');

		for (var c = 0; c < hash.length; c++) {
			var match = hash[c].match(/=/g);
			if ( !!match && match.length == 1){
				var tmp = hash[c].split('=');
				vars[tmp[0]] = tmp[1];
			}
		}
	}
	return vars;
};

_utils.URLHashSet = function (IN){
	var newHash= (typeof IN.newHash == "undefined" ? false : IN.newHash )
	var vars = ( newHash ? Object.create(null) : URLHashParse() );

	for (var prop in IN.vars) {
		if ( IN.vars[prop] === null ){
			delete vars[prop];
		} else {
			vars[prop] = IN.vars[prop];
		}
	}

	var hash = [];
	for (var prop in vars) {
		hash.push(prop + '=' + vars[prop]);
	}
	var hash = '#' + hash.join('&');
	location.hash = hash;
};


_utils.URLQueryParse = function (){
	var vars = {};
	if ( !!location.search ){
		var query = location.search.replace(/^[?]/,"");
		var query = query.split('&');

		for (var c = 0; c < query.length; c++) {
			var match = query[c].match(/=/g);
			if ( match && match.length == 1){
				var tmp = query[c].split('=');
				vars[tmp[0]] = tmp[1];
			}
		}
	}
	return vars;
};

_utils.URLQuerySet = function (IN){
	var newQuery= (typeof IN.newQuery == "undefined" ? false : IN.newQuery );
	var vars = ( newQuery ? Object.create(null) : this.URLQueryParse() );

	for (var prop in IN.vars) {
		if ( IN.vars[prop] === null ){
			delete vars[prop];
		} else {
			vars[prop] = IN.vars[prop];
		}
	}

	var query = [];
	for (var prop in vars) {
		query.push(prop + '=' + vars[prop]);
	}
	var query = '?' + query.join('&');
	location.search = query;
};


_utils.mSelectDBox = function(IN){
	this.init(IN);
};


_utils.mSelectDBox.prototype = {
	"instances": [],

	"init": function(IN){

		this.state = {
			"selectmode" : false
		};

		// Список опций
		this.list = [];

		// Автоматически присваивать value для целевого input (Если это возможно)
		this.autoApply = true;

		// Автоматическое позиционирование (Не работает)
		this.autoPosition = true;

		// Поиск вариантов по списку в момент введения данных через input
		this.autoComplete = true;

		// Целевые элементы
		this.target = null;

		// Можественное выделение
		this.multiple = true;

		// Таймеры
		this.timers = {"ac" : null, "kd" : null, "kd2" : null, "k8": null};

		this.timerInterval = 1000;

		if (
			typeof IN != "undefined"
			&& typeof IN == "object"
			&& typeof IN.list == "object"
		){
			// Целевой элемент
			if (typeof IN.target == "string"){
				this.target = document.querySelectorAll(IN.target);

			} else if ( typeof IN.target == "object" && typeof IN.target.tagName != "undefined" ) {
				this.target = [IN.target];

			} else if ( typeof IN.target == "object" && typeof IN.target.push != "undefined" ){
				var cc=0;
				for ( var c=0; c<IN.target.length; c++ ) {
					if (typeof IN.target[c] == "object" && typeof IN.target[c].tagName != "undefined" ){
						cc++;
					} else {
						break;
					}
				}

				if ( cc == IN.target.length ){
					this.target = IN.target;
				}
			}

			if (!this.target) return;

			delete cc;

			// --------------------------------------------------------------
			// Параметры

			this.onclick = ( typeof IN.onclick == "function" ? IN.onclick : null);

			this.onchange = ( typeof IN.onchange == "function" ? IN.onchange : null);

			this.onkeyup = ( typeof IN.onkeyup == "function" ? IN.onkeyup : null);

			this.onkeydown = ( typeof IN.onkeydown == "function" ? IN.onkeydown : null);

			this.autoApply = ( typeof IN.autoApply == "undefined" ? true : IN.autoApply );

			this.autoPosition = ( typeof IN.autoPosition == "undefined" ? true : IN.autoPosition );

			this.autoComplete = ( typeof IN.autoComplete == "undefined" ? true : IN.autoComplete );

			this.multiple = ( typeof IN.multiple == "undefined" ? true : IN.multiple );

			var zIndex = ( typeof IN.zIndex == "undefined" ? null : IN.zIndex );

			var this_ = this;

			var body = document.querySelector('body');

			// --------------------------------------------------------------

			this_.styles = Object.create(null);

			this_.styles.dboxPaddings = 8;

			if ( !document.querySelector('#mSelectDBoxStyle') ){
				var stylecss = ''
					+'.mSelectDBox {position:absolute; display:block; width:168px; padding:'+this_.styles.dboxPaddings+'px; height:auto; box-shadow:0px 0px 8px rgba(0, 0, 0, 0.24); background-color: #FFF; border-radius: 3px;}'
					+'.mSelectDBox.none {display:none;}'
					+'.mSelectDBox:after {content:\'\'; position:absolute; border-left:10px solid transparent; border-right:9px solid transparent; border-bottom:10px solid white; top: -10px; left: 50%; margin-left: -10px;}'
					+'.mSelectDBox.bottom:after {content:\'\'; position:absolute; border-left:10px solid transparent; border-right:9px solid transparent; border-bottom: none; border-top:10px solid white; top: initial; bottom: -10px; left: 50%; margin-left: -10px;}'
					+'.mSelectDBox ul {position:relative; margin:0px; padding:0px; max-height:200px; overflow-x:hidden;}'
					+'.mSelectDBox li {position:relative; padding:5px; color:black; display:block; line-height:100%; cursor:pointer; font-size:12px;}'
					+'.mSelectDBox li:hover, .mSelectDBox li.hover {background-color:#e6e6e6;}'
					+'.mSelectDBox li.selected {background-color:#C40056; color:white;}'
					+'.mSelectDBox li.selected:hover, .mSelectDBox li.selected.hover {background-color:#DB2277;}'
					+'.mSelectDBox li.selected:before {content:\':: \';}'
					+'.mSelectDBox li:active, .mSelectDBox li.selected:active {background-color:#b80000; color:white;}'
					+'.mSelectDBox li.none {display:none;}'
					+'';

				var style = document.createElement('style');   style.id = "mSelectDBoxStyle";   style.innerHTML = stylecss;   body.appendChild(style);
			}

			// --------------------------------------------------------------
			// Содержимое списка

			this.list = IN.list;

			if ( typeof this.list.push == "undefined" ) return;

			for ( var c=0; c<this.list.length; c++ ){
				if ( ["number","string"].indexOf(typeof this.list[c]) != -1 ){
					this.list[c] = {"value":this.list[c].toString(),"label":this.list[c].toString()};
				} else if ( typeof this.list[c] == "object" ) {

				} else {
					return;
				}
			}

			// --------------------------------------------------------------

			var dbox = document.createElement('div'); dbox.className = "mSelectDBox none";

			this.dbox = dbox;

			if ( !!zIndex ) dbox.style.zIndex = zIndex;

			// --------------------------------------------------------------
			// Заполнение dbox

			this_.buidList();

			// --------------------------------------------------------------
			// Установка событий инпуты

			var ifInputEmptyFx = function(){
				for(var c=0; c<this_.target.length; c++){
					if (
						(
						["input","textarea"].indexOf(this_.target[c].tagName.toLowerCase()) != -1
						|| ["text", "password", "email", "url", "search", "tel"].indexOf(this_.target[c].type.toLowerCase()) != -1
						)
						&& !this_.target[c].value.trim()
					) {
						for(var v=0; v<this_.list.length; v++){
							this_.list[v].selected = false;
						}
						this_.target[c].setAttribute("msdb_value","");
					}
				}
			}


			var openfx = function(e){

				this_.open();

				this_.calcPosition();

				if (
					["input","textarea"].indexOf(this.tagName.toLowerCase()) != -1
					|| (this.type && ["text", "password", "email", "url", "search", "tel"].indexOf(this.type.toLowerCase()) != -1)
				){
					var msdb_value = this.getAttribute('msdb_value');
					if ( !!msdb_value ){ var msdb_value = msdb_value.trim(); }

					// Если в инпуте уже есть значения, отметить их в списке как выбранные
					if (!msdb_value){
						var value = this_.fx.msplit([',',';'],this_.fx.trim(this.value,",; ","both"));
						for(var c=0; c<value.length; c++){
							value[c] = value[c].trim();
							for(var v=0; v<this_.list.length; v++){
								if (this_.list[v].label == value[c]){
									this_.list[v].selected = true;
								}
							}
						}
					} else {
						var msdb_value = this_.fx.msplit([',',';'],msdb_value);
						for(var c=0; c<msdb_value.length; c++){
							msdb_value[c] = msdb_value[c].trim();
							for(var v=0; v<this_.list.length; v++){
								if ( this_.list[v].value == msdb_value[c] ){
									this_.list[v].selected = true;
									break;
								}
							}
						}
					}
				}

				// Все строки
				var dbox_li = this_.dbox.querySelectorAll('li');

				if (!!dbox_li){
					// Снять hover со строки
					for (v=0; v<dbox_li.length; v++){
						if (
							(
							typeof this.type != "undefined"
							&& ["submit","button"].indexOf(this.type.toLowerCase()) > -1
							)
							|| ( ["submit","body","select"].indexOf(this.tagName.toLowerCase()) > -1 )
						){} else {
							dbox_li[v].classList.remove('hover');
						}
						dbox_li[v].classList.remove('none');
					}
				}

				// Записать value внутри инпута
				this_.applySelectedToInput();

				// Отметить выбранные строки
				this_.applySelectedToList();

				if ( typeof this_.onclick == "function" ){
					this_.onclick(this_,e);
				}
			};

			for (var c=0; c<this.target.length; c++){
				this.target[c].addEventListener('click', openfx, null);

				// Расфокусировка
				this.target[c].addEventListener('blur', function(e){
					if (!!e.relatedTarget){
						this_.dbox.style.display = "none";
					}
				}, null);

				// Движение по строкам при помощи клавиатуры, вверх-вниз

				this.target[c].addEventListener('keydown', function(e){
					var target_this = this;

					if (!this_.multiple){
						if ( [37,39,9,18,17,16,20,27].indexOf(e.keyCode) > -1 ){
							// left, right, tab, alt, ctrl, shift, caps, esc

						} else if ( e.keyCode == 13 ){
							// Enter
							if ( this_.dbox.style.display == "none" ){
								this_.dbox.style.display = "block";
								openfx.call(this);
							} else {
								this_.dbox.style.display = "none";
							}
						} else if ( [38,39,40].indexOf(e.keyCode) > -1 ) {
							// other keys

							if ( this_.dbox.style.display != "none" ){

								var ul = this_.dbox.querySelector("ul");

								var li = this_.dbox.querySelectorAll('li:not(.none)');

								var selectedKey = -1;

								if (!li || !li.length){
									return;
								} else {
									for(var c=0; c<li.length; c++){
										if (li[c].classList.contains('selected')){
											var selectedKey = c;
											li[c].classList.remove("selected");
											// break;
										}
									}
								}

								if (selectedKey < 0){
									var selectedKey = 0;
									var prevKey = 0;
									var nextKey = 0;
								} else {
									var prevKey = ( (selectedKey - 1) < 0 ? 0 : selectedKey - 1 );
									var nextKey = ( (selectedKey + 1) >= li.length ? li.length - 1 : selectedKey + 1 );
								}

								var msdbid = li[selectedKey].getAttribute('msdbid');

								if ( e.keyCode == 38 ){
									// up
									var selectedKeyN = prevKey;
								} else if ( e.keyCode == 40 ){
									// down
									var selectedKeyN = nextKey;
								} else if (e.keyCode == 39){
									// right
									var msdbid = li[selectedKey].getAttribute('msdbid');
									this_.list[msdbid].selected = true;
									this_.applySelectedToInput();
									return;
								} else {
									return;
								}

								var pos = $(li[selectedKeyN]).position();

								if (pos.top + 12 >= ul.clientHeight && e.keyCode == 40 ){
									ul.scrollTop += li[selectedKeyN].clientHeight;
								}

								if ( pos.top - 12 <= ul.clientHeight && e.keyCode == 38 ) {
									ul.scrollTop -= li[selectedKeyN].clientHeight;
								}

								for(var c=0; c<this_.list.length; c++){
									this_.list[c].selected = false;
								}

								var msdbidN = parseInt(li[selectedKeyN].getAttribute('msdbid'));

								this_.list[msdbidN].selected = true;

								li[selectedKeyN].classList.add("selected");

								this_.applySelectedToInput();

								var focusedElement = li[selectedKeyN];

								if ( typeof this_.onchange == "function"){
									clearTimeout(this_.timers.kd);
									this_.timers.kd = setTimeout(function(){
										this_.onchange(this_, e);
										this_.calcPosition();
									},this_.timerInterval);
								}

							} // close.display.none
						} // close.if.keys in [38,39,40]
					} // close.if.!multiple

					// --------------------------------------------------------

					if (!!this_.multiple){
						if ( [37,39,9,18,17,16,20,27].indexOf(e.keyCode) > -1 ){
							// left, right, tab, alt, ctrl, shift, caps, esc

						} else if ( e.keyCode == 13 ){
							// Enter
							var hovered_li = this_.dbox.querySelector("ul li.hover");

							if ( !!hovered_li ){
								var msdbid = hovered_li.getAttribute('msdbid');

								if ( !this_.list[msdbid].selected ){
									this_.list[msdbid].selected = true;
								} else {
									this_.list[msdbid].selected = false;
								}

								this_.applySelectedToInput();
								this_.applySelectedToList();

								if ( typeof this_.onchange == "function"){
									clearTimeout(this_.timers.kd2);
									this_.timers.kd2 = setTimeout(function(){
										this_.onchange(this_, e);
										this_.calcPosition();
									},this_.timerInterval);
								}
							}
						} else if ( [38,40].indexOf(e.keyCode) > -1 ) {
							// other keys

							var ul = this_.dbox.querySelector("ul");

							var li = this_.dbox.querySelectorAll('li:not(.none)');

							var hoverKey = -1;

							if (!li || !li.length){
								return;
							} else {
								for(var c=0; c<li.length; c++){
									if (li[c].classList.contains('hover')){
										var hoverKey = c;
										li[c].classList.remove("hover");
										break;
									}
								}
							}

							if (hoverKey < 0){
								var hoverKey = 0;
								var prevKey = 0;
								var nextKey = 0;
							} else {
								var prevKey = ( (hoverKey - 1) < 0 ? 0 : hoverKey - 1 );
								var nextKey = ( (hoverKey + 1) >= li.length ? li.length - 1 : hoverKey + 1 );
							}

							if ( e.keyCode == 38 ){
								// up
								var hoverKeyN = prevKey;
							} else if ( e.keyCode == 40 ){
								// down
								var hoverKeyN = nextKey;
							} else {
								return;
							}

							var pos = $(li[hoverKeyN]).position();

							if (pos.top + 12 >= ul.clientHeight && e.keyCode == 40 ){
								ul.scrollTop += li[hoverKeyN].clientHeight;
							}

							if ( pos.top - 12 <= ul.clientHeight && e.keyCode == 38 ) {
								ul.scrollTop -= li[hoverKeyN].clientHeight;
							}

							li[hoverKeyN].classList.add("hover");
						}
					};

					// --------------------------------------------------------

					if (typeof this_.onkeydown == "function"){
						clearTimeout(this_.timers.kd);
						this_.timers.kd = setTimeout(function(){
							this_.onkeydown(this_, e);
						},100);
					}
				}, null); // close.KeyDown

				// ------------------------------------------------------------

				if (
					["input","textarea"].indexOf(this.target[c].tagName.toLowerCase()) != -1
					|| (this.target[c].type && ["text", "password", "email", "url", "search", "tel"].indexOf(this.target[c].type.toLowerCase()) != -1)
				){
					var cThis = this.target[c];

					this.target[c].addEventListener('keyup', function(e){
						var eThis = this;
						var keyCode = e.keyCode;

						clearTimeout(this_.timers.ac);
						this_.timers.ac = setTimeout(function(){

							// ... autoComplete
							if (this_.autoComplete){
								if ( [37,38,39,40,9,13,18,17,16,20,27].indexOf(keyCode) > -1 ){

								} else {

									// if ( this_.dbox.style.display == "none" ){
									// this_.dbox.style.display = "block";
									// }

									var tmp = this_.dbox.querySelectorAll('li');
									var value = cThis.value.toLowerCase();

									if (this_.multiple){
										var value = this_.fx.msplit([';',','],value);
										var value = value[value.length-1].trim();
									}

									var pattern = new RegExp(value);

									for(var v=0; v<tmp.length; v++){
										var msdbid = parseInt(tmp[v].getAttribute("msdbid"));

										if (!value){
											tmp[v].classList.remove('none');
										} else if ( !this_.list[msdbid].label.toLowerCase().match(pattern) ){
											tmp[v].classList.add('none');
										} else {
											tmp[v].classList.remove('none');
										}

										tmp[v].classList.remove('hover');
									}
								}
							};


							if ( this_.multiple ){
								if ( keyCode == 8 ){
									// keycode 8 - backspace;
									var input_values = this_.fx.trim(eThis.value, " ;,", "both");
									var input_values = this_.fx.msplit([";",","],input_values);

									for(var v=0; v<input_values.length; v++){
										input_values[v] = input_values[v].trim();
									}

									for(var prop in this_.list){
										if (!this_.list.hasOwnProperty(prop)) continue;
										if ( input_values.indexOf(this_.list[prop].label.trim()) == -1 ){
											this_.list[prop].selected = false;
										} else {
											this_.list[prop].selected = true;
										}
									}

									this_.applySelectedToList();
									this_.applySelectedToInput();
								}
							}

							ifInputEmptyFx();

							if (typeof this_.onkeyup == "function"){
								this_.onkeyup(this_, e);
							}

						},500);
					}, null);

					cThis.addEventListener("focus", openfx, null);
				}
			}

			// --------------------------------------------------------------

			var bodyfx = function(e)
			{
				var each = this_.dbox.querySelectorAll('*');

				for (var c=0; c<each.length; c++){
					if ( each[c] == e.target ) return;
				}

				for(var c=0; c<this_.target.length; c++){
					if (this_.target[c] == e.target) return;
				}

				if ( this_.dbox.style.display != "none" ){
					this_.dbox.style.display = "none";
				}
			}

			body.addEventListener('click', bodyfx, null);

			this.instances.push(this);

			body.appendChild(dbox);
		}
	},

	"buidList": function(){
		var ul = document.createElement('ul');

		var this_ = this;

		for(var prop in this.list ){
			if (!this.list.hasOwnProperty(prop)) continue;

			var li = document.createElement('li');
			li.setAttribute('msdbid',prop);

			li.addEventListener(
				'click',
				function(e){
					var msdbid = this.getAttribute('msdbid');

					if (!this_.multiple){
						var tmp = ul.querySelectorAll('li'); var L = tmp.length;
						if ( !!tmp ){
							for(var prop in this_.list){
								this_.list[prop]['selected'] = false;
							}

							for (var c=0; c<L; c++){
								tmp[c].classList.remove("selected");
							}
						}
					}

					var isSelected = (typeof  this_.list[msdbid]['selected'] == "undefined" || !this_.list[msdbid]['selected'] ? false : true );

					if ( isSelected ){
						this.classList.remove('selected');
						this_.list[msdbid]['selected'] = false;
					} else {
						this.classList.add('selected');
						this_.list[msdbid]['selected'] = true;
					}

					// Автоназначение value в целевой input
					if ( this_.autoApply ){
						this_.applySelectedToInput();
					}

					if (!!this_.onchange){
						this_.onchange(this_, e);
					}

					if (!this_.multiple){
						this_.close();
					}

					this_.calcPosition();
				},
				null
			)

			li.addEventListener('mouseleave',function(){
				if (this.classList.contains("hover")){
					this.classList.remove("hover");
				}
			},null);

			li.innerHTML = this.list[prop].label;
			ul.appendChild(li);
		}

		this.dbox.innerHTML = "";

		this.dbox.appendChild(ul);
	},

	"calcPosition" : function(){
		var this_ = this;
		var offset = $(this_.target[0]).offset();
		var thisWidth = this_.target[0].clientWidth;
		var thisHeight = this_.target[0].clientHeight;
		var dboxWidth = this_.dbox.clientWidth;

		this_.dbox.classList.remove("bottom");

		this_.dbox.style.left = (offset.left + (thisWidth / 2) - ((dboxWidth + (this_.styles.dboxPaddings * 2)) / 2)) + "px";

		if ( (this_.dbox.clientHeight + offset.top + thisHeight + 12 - scrollY) > window.innerHeight){
			this_.dbox.style.top = (offset.top - 12 - this_.dbox.clientHeight) + "px";
			this_.dbox.classList.add("bottom");
		} else {
			this_.dbox.style.top = (offset.top + thisHeight + 12) + "px";
		}
	},

	"getSelectedValues": function(){
		var values = [];
		for (var c=0; c<this.list.length; c++){
			if ( typeof this.list[c]['selected'] != "undefined" && !!this.list[c]['selected'] ){
				values.push(this.list[c].value);
			}
		}
		return values;
	},

	"getSelectedLabels": function(){
		var labels = [];
		for (var c=0; c<this.list.length; c++){
			if ( typeof this.list[c]['selected'] != "undefined" && !!this.list[c]['selected'] ){
				labels.push(this.list[c].label);
			}
		}
		return labels;
	},

	"applySelectedToList" : function(){
		var this_ = this;
		var li = this_.dbox.querySelectorAll("li");
		for(var c=0; c<li.length; c++){
			var msdbid = li[c].getAttribute("msdbid");
			if (typeof this_.list[msdbid] != "undefined" && !!this_.list[msdbid].selected ){
				li[c].classList.add('selected');
			} else {
				li[c].classList.remove('selected');
			}
		}
		delete msdbid;
	},

	"applySelectedToInput" : function(){
		var this_ = this;

		var listValue = this_.getSelectedValues();

		var listLabel = this_.getSelectedLabels();

		for (var c=0; c<this_.target.length; c++) {
			var tagName = this_.target[c].tagName.toLowerCase();
			if ( tagName == "input" ){
				if (this_.target[c].type && ["text", "password", "email", "url", "search", "tel"].indexOf(this_.target[c].type.toLowerCase()) != -1 ){
					this_.target[c].value = listLabel.join("; ") + (!listLabel.length ? "" : ";");
				}
			} else if ( tagName == "textarea" ) {
				this_.target[c].value = listLabel.join("; ") + (!listLabel.length ? "" : ";");
			} else if ( tagName == "select" ) {
				for (var v=0; v<target[c].options.length; v++) {
					if ( listValue.indexOf(target[c].options[v].value != -1) ){
						target[c].options[v].selected = true;
					} else {
						target[c].options[v].selected = false;
					}
				}
			} else {
				// this_.target[c].innerHTML = listLabel.join("; ") + (!listLabel.length ? "" : ";");
			}
			this_.target[c].setAttribute("msdb_value", listValue.join(";") + (!listValue.length ? "" : ";"));
		}
	},

	"close" : function(){
		this.dbox.style.display = "none";
	},

	"open" : function(){
		this.dbox.style.display = "block";
	},

	"fx" : {
		"msplit" : function(d,s){
			var s = s.replace(new RegExp('['+d.join('')+']','g'),d[0]);
			return s.split(d[0]);
		},

		"trim" : function(str,_chars,_mode){
			var str = str.split('');

			if ( typeof _chars == "string" ){
				var _chars = _chars.split('');
			} else if ( typeof _chars == "object" && typeof _chars.push != "undefined" ){

			} else {
				return str.join('');
			}

			if ( typeof _mode == "undefined" ) {var _mode = 'both'; }

			if ( _mode == 'both' ){
				for(;;){
					if ( !str.length || !(_chars.indexOf(str[0]) != -1 || _chars.indexOf(str[str.length-1]) != -1) ) break;
					if ( _chars.indexOf(str[str.length-1]) != -1 ) str.pop();
					if ( _chars.indexOf(str[0]) != -1 ) str.shift();
				}
			}

			if ( _mode == 'left' ){
				for(;;){
					if ( !str.length || _chars.indexOf(str[0]) == -1 ) break;
					str.shift();
				}
			}

			if ( _mode == 'right' ){
				for(;;){
					if ( !str.length || _chars.indexOf(str[str.length-1]) == -1 ) break;
					str.pop();
				}
			}

			return str.join('');
		}
	}
}