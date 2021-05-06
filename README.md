## XiunoBBS
------
因为一些众所周知的原因，**老黄** 关闭并移除了```xiunobbs```相关的站点与资源。   
因本人在不久前有 `clone` **XiunoBBS** 的最新源码，故庆幸可以再次分发到本仓库。后续的维护期待大家参与。
 
请各位使用者遵守相关法律法规，严禁使用本程序搭建 **违法、侵权 及 擦边** 的网站，请**自行承担法律风险**。   

本仓库无站点、无QQ交流群，只保留 **[wiki](https://github.com/xiulab/xiunobbs/wiki)**、**[issues](https://github.com/xiulab/xiunobbs/issues)**。 
- **GitHub:**  https://github.com/xiulab/xiunobbs   
- **Gitee:** https://gitee.com/xiulab/xiunobbs   

**更多教程请查看 Wiki**: https://github.com/xiulab/xiunobbs/wiki 

------
### 【更新说明】
- 原仓库关闭时，代码最后更新的时间为 `2020年5月8日`，其分支名为 **[master](tree/master)**。
- 因 `xiuno.com` 站点关闭，插件中心已失效，故最新代码已移除插件中心。
- 已将原代码中的所有插件独立出来成为一个仓库: [https://github.com/xiulab/xn_plugins](https://github.com/xiulab/xn_plugins)

------
### 【Xiuno BBS 4.0 是什么？】
Xiuno BBS 4.0 是一款轻论坛产品，前端基于 BootStrap 4.0、JQuery 3，后端基于 PHP/7 MySQL XCache/Yac/Redis/Memcached...

自适应手机、平板、PC，有着非常方便的插件机制，不仅仅是一个轻论坛，还是一个良好的二次开发平台。

------
### 【Xiuno BBS 4.0 带来了什么？】
前端采用 BootStrap 4 + JQuery 3，响应式布局，自适应手机，平板，PC 设备，不再需要单独开发移动版本。

对 Bootstrap 4 进行了增强和兼容，比如增加 $('#submit').button('xxx').delay(3000).location('xxx.php') 的连续操作支持。

xiuno.js 采用了 xn. 命名空间，不再担心 js 命名冲突，完善了对常用的 php 函数的实现。

增加了通用的 $.each_sync() 方法，从客户端避免 ajax 并发导致的服务端并发写数据问题，简化了服务端逻辑。

不再支持 IE89 和以下版本，全面拥抱移动端，不用再用琢磨恶心的 css hack。

不再强制要求 URL-Rewrite， 采用相对路径的 URL 格式，方便部署到子目录：user-login.htm

图片缩略、裁切放到了客户端，不再依赖服务端 GD 库（不再担心各种 GD 漏洞和弱点）。

同时支持 Session 和 Token 方式登录，可以全站返回 json 数据，方便 APP 开发。

插件机制采用 hook + overwrite 方式，方便插入，和覆盖，非常方便二次开发，并且不影响性能，不影响编译。

db 层采用了更加方便的接口，可以同时支持 SQL 和 NoSQL 的方式操作数据（提倡 NoSQL)。

论坛功能上更加的精简，更多功能采用插件的方式进行扩充。

引入了语言包，自带简体、繁体、英文三个版本。

<del>插件中心正式开启，开发者可以入驻，开发收费插件。</del>

只需要一个博客插件，它就可以变成一个功能强大的博客。

帖子支持 txt html markdown 三种格式，自带适度整合的 UMEditor 插件，修正了 UM 在 Bootstrap 4 下的很多问题。

xiunophp 4.0 这个框架合并成了一个文件 xiunophp.min.php，只需要一个 include 就可以开始使用里面提供的方便的函数和全局变量。

Xiuno BBS 4 正式版经历了近 2 年，7 个 beta 版本，最终定型，这可能是最后一个大版本，放心动手二次开发吧。

------
### 【性能方面】
采用静态语言编程风格，充分发挥 PHP7 OPCache 的威力。

专门针对 BBS 业务的索引优化和适度的缓存。

大量的运算放到了客户端，并发问题尽量由客户端控制。

作者十多年从业经验带领您绕过雷区。

------
### 【授权】
Xiuno BBS 4.0 采用 MIT 协议发布，您可以自由修改、派生版本、商用而不用担心任何法律风险（修改后应保留原来的版权信息）。
