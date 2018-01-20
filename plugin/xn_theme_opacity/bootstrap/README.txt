风格制作帮助文档


1. 风格插件的说明：
plugin/xn_theme_opacity

命名规范：
xn_：作者姓名缩写，小写英文加下划线
theme_：表示是风格类型的插件
opacity：风格的名字


2. 目录结构说明：
+ bootstrap
	+ scss  SCSS 目录
		+ mixins
		+ utilities
		. _alert.scss
		. _badge.scss
		. ...scss
		. _variables.scss 全局的样式的值，修改里面的变量的值后重新编译
	+ css
		bootstrap.css		最终生成的文件，会被用到的
		bootstrap-bbs.css	最终生成的文件，会被用到的
	hook
		header_bootstrap_before.htm   hook 到 view/htm/header.inc.htm 		
		header_link_before.htm   hook 到 view/htm/header.inc.htm 
		plugin_umeditor_js_css_after.htm  百度编辑器美化

3. 如何编译？
  3.1 首先安装 nodejs：
  3.2 然后 npm install node-sass
  3.3 然后双击 auto.cmd 就可以开始编译了。
  3.4 最后生成的文件在 css 目录下。
