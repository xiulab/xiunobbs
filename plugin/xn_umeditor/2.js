

UM.registerUI('insertcode', function( name ) {

	 var me = this;
	 
	/* var lang_options = {
		'as3':'ActionScript 3',
		'bash':'Bash/Shell',
		'cpp':'C/C++',
		'css':'CSS',
		'cf':'ColdFusion',
		'c#':'C#',
		'delphi':'Delphi',
		'diff':'Diff',
		'erlang':'Erlang',
		'groovy':'Groovy',
		'html':'HTML',
		'java':'Java',
		'jfx':'JavaFX',
		'js':'JavaScript',
		'pl':'Perl',
		'php':'PHP',
		'plain':'Plain Text',
		'ps':'PowerShell',
		'python':'Python',
		'ruby':'Ruby',
		'scala':'Scala',
		'sql':'SQL',
		'vb':'Visual Basic',
		'xml':'XML'
	};
	*/
	 var options = {
		autoRecord: false,
		label: '插入代码',
		title: '插入代码',
		comboboxName: 'insertcode',
		items: ["C/C++", "PHP"],
		itemStyles: ["", ""],
		value: ["C/C++", "PHP"],
		autowidthitem: []
	}
		
		 //实例化
	$combox =  $.eduibuttoncombobox(options).css('zIndex', me.getOpt('zIndex') + 1);
	comboboxWidget =  $combox.edui();

	comboboxWidget.on('comboboxselect', function( evt, res ){
		me.execCommand('insertcode', res.value);
	}).on("beforeshow", function(){
		if( $combox.parent().length === 0 ) {
			$combox.appendTo(  me.$container.find('.edui-dialog-container') );
		}
		UM.setTopEditor(me);
	});
	
	//状态反射
	this.addListener('selectionchange',function( evt ){

		var state  = this.queryCommandState( name ),
		value = this.queryCommandValue( name );

		//设置按钮状态
		comboboxWidget.button().edui().disabled( state == -1 ).active( state == 1 );
		if (state == -1) {
			// todo:
		  	//ui.setDisabled(true);
		} else {
		  	// todo:
		  	//ui.setDisabled(false);
		  	var value = editor.queryCommandValue('insertcode');
		  	if(!value){
			   //ui.setValue(title);
			   return;
		  	}
		  	//trace:1871 ie下从源码模式切换回来时，字体会带单引号，而且会有逗号
		  	value && (value = value.replace(/['"]/g, '').split(',')[0]);
		  	//ui.setValue(value);
		  }


	});
	 return comboboxWidget.button().addClass('edui-combobox');
});





/*



editorui.insertcode = function (editor, list, title) {
	 list = editor.options['insertcode'] || [];
	 title = editor.options.labelMap['insertcode'] || editor.getLang("labelMap.insertcode") || '';
	// if (!list.length) return;
	 var items = [];
	 utils.each(list,function(key,val){
	     items.push({
		  label:key,
		  value:val,
		  theme:editor.options.theme,
		  renderLabelHtml:function () {
		      return '<div class="edui-label %%-label" >' + (this.label || '') + '</div>';
		  }
	     });
	 });

	 var ui = new editorui.Combox({
	     editor:editor,
	     items:items,
	     onselect:function (t, index) {
		  editor.execCommand('insertcode', this.items[index].value);
	     },
	     onbuttonclick:function () {
		  this.showPopup();
	     },
	     title:title,
	     initValue:title,
	     className:'edui-for-insertcode',
	     indexByValue:function (value) {
		  if (value) {
		      for (var i = 0, ci; ci = this.items[i]; i++) {
			   if (ci.value.indexOf(value) != -1)
				return i;
		      }
		  }

		  return -1;
	     }
	 });
	 editorui.buttons['insertcode'] = ui;
	 editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
	     if (!uiReady) {
		  var state = editor.queryCommandState('insertcode');
		  if (state == -1) {
		      ui.setDisabled(true);
		  } else {
		      ui.setDisabled(false);
		      var value = editor.queryCommandValue('insertcode');
		      if(!value){
			   ui.setValue(title);
			   return;
		      }
		      //trace:1871 ie下从源码模式切换回来时，字体会带单引号，而且会有逗号
		      value && (value = value.replace(/['"]/g, '').split(',')[0]);
		      ui.setValue(value);

		  }
	     }

	 });
	 return ui;
    };*/


